<?php

namespace App\Imports;

use App\Models\ProductVariant;
use App\Models\StockEntry;
use App\Services\SystemTime;
use Maatwebsite\Excel\Concerns\ToArray;

/**
 * StockImport — Intelligent spreadsheet stock intake importer.
 *
 * Key design decisions:
 *  • Uses ToArray (not ToCollection + WithHeadingRow) so we receive the full
 *    raw 2-D array and can scan for the real header row ourselves, even when
 *    client files have one or more title rows above the column headers.
 *  • detectHeaderRowIndex() scans the first N rows and identifies the header
 *    row by counting how many cells match our column-alias dictionary (≥ 2 hits).
 *  • isNoiseRow() silently drops blank, too-long, or section-header rows without
 *    counting them as errors.
 *  • Every real skip (SKU not found, quantity missing) produces a clear,
 *    actionable error message that references the original row number.
 */
class StockImport implements ToArray
{
    public int   $imported = 0;
    public int   $skipped  = 0;
    public int   $noise    = 0;   // silently dropped title/blank rows — not shown as errors
    public array $errors   = [];

    private ?int    $userId;
    private ?string $customNotes;
    private string  $mode;
    private ?string $branchName;

    // ── Column aliases ───────────────────────────────────────────────────────
    // Any of these raw header texts (after normalising) map to the canonical key.
    private const COL_ALIASES = [
        'sku' => [
            'sku', 'code', 'item_code', 'itemcode', 'barcode', 'product_code',
            'article', 'article_no', 'articleno', 'ref', 'reference', 'id',
        ],
        'product_name' => [
            'product_name', 'product', 'item_name', 'itemname', 'item',
            'uniform', 'name', 'description', 'desc', 'title', 'productname',
            'uniform_name', 'style', 'style_name', 'garment', 'article_name',
        ],
        'colour' => ['colour', 'color', 'col', 'clr'],
        'size' => [
            'size', 'sizes', 'sz', 'size_name', 'sizename', 'dimension',
        ],
        'quantity' => [
            'quantity', 'qty', 'stock', 'initial_stock', 'opening_stock',
            'opening_qty', 'units', 'count', 'opening', 'balance', 'bal',
            'on_hand', 'onhand', 'available',
        ],
        'selling_price' => [
            'selling_price', 'price', 'unit_price', 'unitprice', 'retail',
            'retail_price', 'sell_price', 'rate', 'sell',
        ],
    ];

    // ── Noise keywords ───────────────────────────────────────────────────────
    // A row whose first cell starts with or contains any of these strings is
    // silently discarded (not counted as a user-facing skipped row).
    private const NOISE_KEYWORDS = [
        'stock card', 'month of', 'report', 'for the', 'grand total',
        'total', 'summary', 'page', 'date:', 'branch:', 'prepared by',
        'subtotal', 'item no', 'item #', 'serial no', '---', '===',
    ];

    public function __construct(
        ?int    $userId      = null,
        ?string $customNotes = null,
        string  $mode        = 'replace',
        ?string $branchName  = null
    ) {
        $this->userId      = $userId;
        $this->customNotes = $customNotes;
        $this->mode        = in_array($mode, ['replace', 'add']) ? $mode : 'replace';
        $this->branchName  = $branchName ? trim($branchName) : null;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private const CATEGORY_MAP = [
        'sweaters'     => 'School Sweaters',
        'sweater'      => 'School Sweaters',
        'pullover'     => 'School Sweaters',
        'jersey'       => 'School Sweaters',
        'sleeveless'   => 'School Sweaters',
        'tracksuits'   => 'Tracksuits',
        'tracksuit'    => 'Tracksuits',
        'track suit'   => 'Tracksuits',
        't-shirts'     => 'T-Shirts & Polo',
        't-shirt'      => 'T-Shirts & Polo',
        'tshirt'       => 'T-Shirts & Polo',
        'polo'         => 'T-Shirts & Polo',
        'fleece'       => 'Fleece Jackets',
        'jackets'      => 'Fleece Jackets',
        'jacket'       => 'Fleece Jackets',
    ];

    private function categoryFromName(string $name): string
    {
        $lower = strtolower($name);
        foreach (self::CATEGORY_MAP as $keyword => $catName) {
            if (str_contains($lower, $keyword)) {
                return $catName;
            }
        }
        return 'General Uniforms';
    }

    /** Strip all non-alphanumeric chars and lowercase — matches most header variants. */
    private function normaliseKey(string $key): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $key));
    }

    /**
     * Build a reverse alias lookup: normalisedAlias => canonicalKey.
     * @return array<string, string>
     */
    private function aliasLookup(): array
    {
        static $lookup = null;
        if ($lookup === null) {
            $lookup = [];
            foreach (self::COL_ALIASES as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    $lookup[$this->normaliseKey($alias)] = $canonical;
                }
            }
        }
        return $lookup;
    }

    /**
     * Scan the first $scanLimit rows and return the index of the best-candidate
     * header row (≥ 2 cells match known column aliases).
     * Returns -1 if none found (caller will fall back to row 0).
     */
    private function detectHeaderRowIndex(array $rows, int $scanLimit = 10): int
    {
        $lookup = $this->aliasLookup();
        $limit  = min($scanLimit, count($rows));
        $best   = ['index' => -1, 'score' => 0];

        for ($i = 0; $i < $limit; $i++) {
            $score = 0;
            foreach ($rows[$i] as $cell) {
                $norm = $this->normaliseKey((string)($cell ?? ''));
                if ($norm !== '' && isset($lookup[$norm])) {
                    $score++;
                }
            }
            if ($score >= 2 && $score > $best['score']) {
                $best = ['index' => $i, 'score' => $score];
            }
        }

        return $best['index'];
    }

    /**
     * Build a map from column index (0-based) → canonical column name.
     * @return array<int, string>
     */
    private function buildColumnMap(array $headerRow): array
    {
        $lookup = $this->aliasLookup();
        $map    = [];

        foreach ($headerRow as $colIndex => $cell) {
            $norm = $this->normaliseKey((string)($cell ?? ''));
            if ($norm !== '' && isset($lookup[$norm])) {
                // Only assign the first occurrence (avoid duplicate canonical keys)
                if (!in_array($lookup[$norm], $map, true)) {
                    $map[$colIndex] = $lookup[$norm];
                }
            }
        }

        return $map;
    }

    /**
     * Return true if this row is noise (blank, section header, repeated title, etc.)
     * and should be silently discarded without an error message.
     */
    private function isNoiseRow(array $row): bool
    {
        $values = array_map('trim', array_map('strval', $row));

        // Entirely blank row
        if (empty(array_filter($values))) {
            return true;
        }

        $first     = $values[0] ?? ($values[array_key_first($values)] ?? '');
        $firstLow  = mb_strtolower($first);

        // First cell too long to be a SKU, product name, or size (likely a title)
        if (mb_strlen($first) > 60) {
            return true;
        }

        // Contains known noise keyword phrases
        foreach (self::NOISE_KEYWORDS as $kw) {
            if (str_contains($firstLow, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a canonical column value from an indexed row using the column map.
     */
    private function resolveColumn(array $row, array $colMap, string $canonical): string
    {
        foreach ($colMap as $colIndex => $can) {
            if ($can === $canonical && array_key_exists($colIndex, $row)) {
                return trim((string)($row[$colIndex] ?? ''));
            }
        }
        return '';
    }

    // ── Main entry point ─────────────────────────────────────────────────────

    /**
     * Receives the full raw 2-D array from Maatwebsite Excel.
     * First we find the real header row, then process every data row after it.
     */
    public function array(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $now   = SystemTime::now();
        $notes = $this->customNotes
            ?: ('Spreadsheet Opening Stock Intake on ' . $now->format('d M Y H:i'));

        // ── 1. Find the header row ────────────────────────────────────────────
        $headerRowIndex = $this->detectHeaderRowIndex($rows);

        if ($headerRowIndex === -1) {
            // No recognisable header found — assume row 0 is the header
            $headerRowIndex = 0;
        }

        $colMap  = $this->buildColumnMap($rows[$headerRowIndex]);
        $dataRows = array_slice($rows, $headerRowIndex + 1);

        // ── 2. Create the parent StockEntry record ────────────────────────────
        $entry = StockEntry::create([
            'user_id'      => $this->userId,
            'reference_no' => 'IMPORT-' . $now->format('YmdHis'),
            'notes'        => $notes,
        ]);

        // ── 3. Process each data row ──────────────────────────────────────────
        foreach ($dataRows as $dataIndex => $row) {
            // Real spreadsheet row number (1-indexed, accounting for header offset)
            $rowNum = $headerRowIndex + $dataIndex + 2;

            // --- Noise filter: silently discard ---
            if ($this->isNoiseRow($row)) {
                $this->noise++;
                continue;
            }

            // --- Resolve fields by column map ---
            $sku      = strtoupper($this->resolveColumn($row, $colMap, 'sku'));
            $prodName = $this->resolveColumn($row, $colMap, 'product_name');
            $colour   = $this->resolveColumn($row, $colMap, 'colour');
            $size     = $this->resolveColumn($row, $colMap, 'size');
            $qtyRaw   = $this->resolveColumn($row, $colMap, 'quantity');
            $priceRaw = $this->resolveColumn($row, $colMap, 'selling_price');

            // Positional fallback when colMap didn't cover a column
            // (useful when headers are partially unrecognised)
            $values = array_values($row);
            if ($sku === '' && isset($values[0]))      $sku      = strtoupper(trim((string)$values[0]));
            if ($prodName === '' && isset($values[1])) $prodName = trim((string)$values[1]);
            if ($size === '' && isset($values[2]))     $size     = trim((string)$values[2]);
            if ($qtyRaw === '' && isset($values[3]))   $qtyRaw   = trim((string)$values[3]);

            // --- Skip if all identifying fields are empty ---
            if ($sku === '' && $prodName === '' && $size === '') {
                $this->noise++;
                continue;
            }

            // --- Skip if quantity is non-numeric (e.g. a repeated header or label) ---
            $qtyStr   = str_replace(',', '', $qtyRaw);
            $qtyClean = preg_replace('/[^0-9.\-]/', '', $qtyStr);
            if ($qtyRaw !== '' && $qtyClean === '' && !is_numeric($qtyStr)) {
                $this->noise++;
                continue;
            }

            $quantity = (int) round((float)($qtyClean ?: '0'));

            if ($quantity < 0) {
                $this->skipped++;
                $this->errors[] = "Row #{$rowNum}: Skipped — Quantity cannot be negative.";
                continue;
            }

            // --- 1. Match by SKU ---
            $variant = null;
            if ($sku !== '') {
                $variant = ProductVariant::where('sku', $sku)->first();
            }

            // --- 2. Fallback: match by Product Name + Size ---
            if (!$variant && $prodName !== '' && $size !== '') {
                $variant = ProductVariant::whereHas('product', function ($q) use ($prodName) {
                    $q->where('name', 'like', "%{$prodName}%");
                })->where('size', $size)->first();
            }

            // --- 3. Auto-create variant when sufficient data is present ---
            if (!$variant) {
                if ($prodName !== '' && $size !== '') {
                    $catName = $this->categoryFromName($prodName);
                    $cat     = \App\Models\Category::where('name', $catName)->first()
                               ?? \App\Models\Category::firstOrCreate(['name' => $catName]);

                    $product = \App\Models\Product::where('name', $prodName)->first();
                    if (!$product) {
                        $basePrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prodName), 0, 4)) ?: 'PRD';
                        $finalPrefix = $basePrefix;
                        $counter = 2;
                        while (\App\Models\Product::where('sku_prefix', $finalPrefix)->exists()) {
                            $finalPrefix = $basePrefix . $counter++;
                        }
                        $product = \App\Models\Product::create([
                            'name'        => $prodName,
                            'sku_prefix'  => $finalPrefix,
                            'category_id' => $cat->id,
                        ]);
                    }

                    $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
                    $baseSku   = $sku !== '' ? $sku : "{$product->sku_prefix}-{$cleanSize}";
                    $finalSku  = $baseSku;
                    $vCount    = 2;
                    while (ProductVariant::where('sku', $finalSku)->exists()) {
                        $finalSku = $baseSku . '-V' . $vCount++;
                    }

                    $variant = ProductVariant::create([
                        'product_id'      => $product->id,
                        'size'            => $size,
                        'sku'             => $finalSku,
                        'barcode'         => 'BC-' . $finalSku,
                        'colour'          => $colour !== '' ? $colour : 'Standard',
                        'unit_of_measure' => 'Pieces',
                        'selling_price'   => is_numeric($priceRaw) && (float)$priceRaw > 0
                                                ? (float)$priceRaw : 0.00,
                        'stock_quantity'  => 0.00,
                    ]);
                } else {
                    // Not enough data to identify or create — emit actionable error
                    $this->skipped++;
                    $label = $sku !== ''
                        ? "SKU '{$sku}'"
                        : "product '" . ($prodName ?: '(empty)') . "' + size '" . ($size ?: '(empty)') . "'";
                    $this->errors[] = "Row #{$rowNum}: Skipped — No matching product found for {$label}."
                        . " Use the Download Template to get the correct SKU list.";
                    continue;
                }
            }

            // --- Record the entry item ---
            $entry->items()->create([
                'product_variant_id' => $variant->id,
                'quantity'           => $quantity,
            ]);

            // --- Apply stock update ---
            if ($this->branchName !== null && $this->branchName !== '') {
                if ($this->mode === 'replace') {
                    $variant->setStockForBranch($this->branchName, $quantity);
                } else {
                    $variant->adjustStockForBranch($this->branchName, $quantity);
                }
            } else {
                if ($this->mode === 'replace') {
                    $variant->update(['stock_quantity' => $quantity]);
                } else {
                    $variant->increment('stock_quantity', $quantity);
                }
            }

            // --- Sync selling price if provided and > 0 ---
            if (is_numeric($priceRaw) && (float)$priceRaw > 0) {
                $variant->update(['selling_price' => (float)$priceRaw]);
            }

            $this->imported++;
        }
    }
}
