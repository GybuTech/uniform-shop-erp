<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * ProductCatalogImport — Multi-format intelligent catalog & stock spreadsheet importer.
 *
 * Automatically detects and processes THREE real-world layouts:
 *
 * 1. MULTI-SHEET RETAIL STOCK-CARD WORKBOOKS (.xlsx / .xls)
 *    e.g. "STALL 35 UPDATED STOCK SHEETS..xlsx"
 *    Sheets: SWEATERS STOCK., TRACKSUITS STOCK., T-SHIRTS STOCK., FLEECE JACKETS.
 *    Cols: COLOUR (Product) | SIZE | Opening Stock | Daily tallies...
 *    Auto-assigns products to categories:
 *      - Tracksuits (Accurate Tracksuit, Doonholm Junior Tracksuit, etc.)
 *      - School Sweaters (Grey/Red Sweater, Imara Sweater, etc.)
 *      - T-Shirts & Polo (Maroon Normal T-Shirt, etc.)
 *      - Fleece Jackets (N/Blue Fleece Jacket, etc.)
 *
 * 2. WIDE / PIVOT SHEETS (sizes as column headers)
 *    e.g. SCHOOL/ITEM | 24 | 26 | 28 | 30 | TOTAL
 *
 * 3. TALL CSV TEMPLATES
 *    e.g. category_name | product_name | sku_prefix | size | colour | selling_price | initial_stock
 */
class ProductCatalogImport implements ToArray
{
    public int   $importedProducts    = 0;
    public int   $importedVariants    = 0;
    public int   $importedCategories  = 0;
    public int   $updatedVariants     = 0;
    public int   $skipped             = 0;
    public int   $noise               = 0;
    public array $errors              = [];

    protected string $branchName;

    private const CATEGORY_MAP = [
        'sweaters'     => 'School Sweaters',
        'sweater'      => 'School Sweaters',
        'pullover'     => 'School Sweaters',
        'jersey'       => 'School Sweaters',
        'jumper'       => 'School Sweaters',
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
        'blazer'       => 'Outerwear',
        'shirt'        => 'Shirts & Blouses',
        'blouse'       => 'Shirts & Blouses',
        'trouser'      => 'Trousers & Shorts',
        'short'        => 'Trousers & Shorts',
        'skirt'        => 'Skirts & Dresses',
        'dress'        => 'Skirts & Dresses',
    ];

    private const DEFAULT_PRICES = [
        'School Sweaters'  => 1200.00,
        'Tracksuits'      => 1500.00,
        'Fleece Jackets'  => 1500.00,
        'T-Shirts & Polo' => 500.00,
        'General Uniforms'=> 800.00,
    ];

    private const COL_ALIASES = [
        'category_name' => ['category_name', 'category', 'cat', 'type', 'department', 'school'],
        'product_name'  => ['product_name', 'product', 'item_name', 'item', 'uniform', 'name', 'description', 'particulars', 'style', 'garment', 'article', 'schoolitem', 'schoolname', 'itemno', 'articleno', 'uniformname'],
        'sku_prefix'    => ['sku_prefix', 'prefix'],
        'sku'           => ['sku', 'code', 'item_code', 'barcode', 'article_no', 'ref'],
        'size'          => ['size', 'sizes', 'sz', 'size_name', 'dimension', 'age'],
        'colour'        => ['colour', 'color', 'col', 'clr'],
        'selling_price' => ['selling_price', 'price', 'unit_price', 'rate', 'retail'],
        'initial_stock' => ['initial_stock', 'stock', 'quantity', 'qty', 'count', 'balance', 'opening_stock', 'opening_qty', 'pieces', 'units', 'on_hand'],
    ];

    public function __construct(string $branchName = 'Main Shop')
    {
        $this->branchName = trim($branchName) ?: 'Main Shop';
    }

    /**
     * Master entry point called directly with file path.
     * Can process multi-sheet workbooks or single sheets.
     */
    public function importFile(string $filePath, string $extension): void
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $spreadsheet = IOFactory::load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        // Check if workbook contains stock card format (COLOUR | SIZE | Opening Stock)
        $isStockCardFormat = false;
        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $previewRows = $sheet->toArray(null, true, true, false);
            foreach (array_slice($previewRows, 0, 8) as $row) {
                $c0 = strtoupper(trim((string)($row[0] ?? '')));
                $c1 = strtoupper(trim((string)($row[1] ?? '')));
                if (($c0 === 'COLOUR' || $c0 === 'COLOR') && ($c1 === 'SIZE' || str_contains($c1, 'SZ'))) {
                    $isStockCardFormat = true;
                    break 2;
                }
            }
        }

        if ($isStockCardFormat) {
            $this->processMultiSheetStockCards($spreadsheet);
        } else {
            // Process sheets individually using standard wide/tall detectors
            $allRows = [];
            foreach ($sheetNames as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray(null, true, true, false);
                if (!empty($rows)) {
                    $this->array($rows);
                }
            }
        }
    }

    /**
     * Process physical stock-card workbooks (like STALL 35 UPDATED STOCK SHEETS..xlsx).
     */
    private function processMultiSheetStockCards($spreadsheet): void
    {
        $branch = Branch::where('name', $this->branchName)->first()
            ?? Branch::firstOrCreate(
                ['name' => $this->branchName],
                ['code' => 'BR-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->branchName), 0, 4))]
            );

        $catCache    = Category::all()->keyBy(fn($c) => strtolower($c->name));
        $prodCache   = Product::all()->keyBy(fn($p) => strtolower($p->name));
        $skuCache    = ProductVariant::all()->keyBy(fn($v) => strtoupper($v->sku));
        $prodSzCache = ProductVariant::all()->keyBy(fn($v) => $v->product_id . '___' . strtolower($v->size));

        // Aggregate stock data across sheets: [CategoryName][ProductName][Size] = stock
        $catalog = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows  = $sheet->toArray(null, true, true, false);

            $sheetLower = strtolower($sheetName);
            $defaultCategory = 'General Uniforms';
            foreach (self::CATEGORY_MAP as $key => $cat) {
                if (str_contains($sheetLower, $key)) {
                    $defaultCategory = $cat;
                    break;
                }
            }

            $currentProduct = null;
            $currentCategory = $defaultCategory;

            foreach ($rows as $row) {
                $col0 = trim((string)($row[0] ?? ''));
                $col1 = trim((string)($row[1] ?? ''));
                $col2 = trim((string)($row[2] ?? ''));

                // Title row defining a category (e.g. SWEATERS STOCK CARD...)
                if ($col1 === '' && $col2 === '') {
                    foreach (self::CATEGORY_MAP as $k => $c) {
                        if (str_contains(strtolower($col0), $k)) {
                            $currentCategory = $c;
                            break;
                        }
                    }
                    continue;
                }

                // Skip header row (COLOUR | SIZE | Opening Stock)
                if (strtoupper($col0) === 'COLOUR' || strtoupper($col0) === 'COLOR' || strtoupper($col1) === 'SIZE') {
                    continue;
                }

                if ($col1 !== '') {
                    if ($col0 !== '') {
                        $currentProduct = $this->formatUniformProductName($col0, $currentCategory);
                    }

                    if ($currentProduct !== null) {
                        $cleanQty = preg_replace('/[^0-9.]/', '', str_replace(',', '', $col2));
                        $qty = is_numeric($cleanQty) ? (float)$cleanQty : 0.0;

                        if (!isset($catalog[$currentCategory][$currentProduct][$col1])) {
                            $catalog[$currentCategory][$currentProduct][$col1] = $qty;
                        } else {
                            $catalog[$currentCategory][$currentProduct][$col1] += $qty;
                        }
                    }
                }
            }
        }

        // Now save all products and variants in a single transaction
        DB::beginTransaction();
        try {
            foreach ($catalog as $categoryName => $products) {
                $category = $this->ensureCategory($categoryName, $catCache);

                foreach ($products as $prodName => $sizes) {
                    $skuPrefix = $this->generateSkuPrefix($prodName);
                    $product = $this->ensureProduct($prodName, $skuPrefix, $category, $prodCache);

                    $defaultPrice = self::DEFAULT_PRICES[$categoryName] ?? 1000.00;

                    foreach ($sizes as $size => $stock) {
                        $this->upsertVariant(
                            $product,
                            (string)$size,
                            'Standard',
                            '',
                            $defaultPrice,
                            $stock,
                            $branch,
                            $skuCache,
                            $prodSzCache
                        );
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Standardizes uniform product names and ensures correct category descriptor:
     * e.g. ACCURATE in Tracksuits -> Accurate Tracksuit
     *      GREY/RED in School Sweaters -> Grey/Red Sweater
     *      MAROON NORMAL in T-Shirts -> Maroon Normal T-Shirt
     */
    private function formatUniformProductName(string $rawName, string $categoryName): string
    {
        $clean = trim($rawName);
        $clean = ucwords(strtolower($clean));
        $clean = preg_replace_callback('/\/([a-z])/', fn($m) => '/' . strtoupper($m[1]), $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        if ($categoryName === 'Tracksuits') {
            if (!str_contains(strtolower($clean), 'tracksuit')) {
                $clean .= ' Tracksuit';
            }
        } elseif ($categoryName === 'School Sweaters') {
            if (!str_contains(strtolower($clean), 'sweater') &&
                !str_contains(strtolower($clean), 'pullover') &&
                !str_contains(strtolower($clean), 'sleeveless')) {
                $clean .= ' Sweater';
            }
        } elseif ($categoryName === 'T-Shirts & Polo') {
            if (!str_contains(strtolower($clean), 't-shirt') &&
                !str_contains(strtolower($clean), 'polo') &&
                !str_contains(strtolower($clean), 'shirt')) {
                $clean .= ' T-Shirt';
            }
        } elseif ($categoryName === 'Fleece Jackets') {
            if (!str_contains(strtolower($clean), 'jacket') &&
                !str_contains(strtolower($clean), 'fleece')) {
                $clean .= ' Fleece Jacket';
            }
        }

        return $clean;
    }

    private function generateSkuPrefix(string $prodName): string
    {
        $words = preg_split('/[\s\/\-]+/', trim($prodName));
        $prefix = '';
        foreach ($words as $w) {
            $cleanW = preg_replace('/[^A-Za-z0-9]/', '', $w);
            if ($cleanW !== '') {
                $prefix .= strtoupper(substr($cleanW, 0, 3));
            }
        }
        return substr($prefix ?: 'PRD', 0, 15);
    }

    // ── FALLBACK / TOARRAY SUPPORT ──────────────────────────────────────────

    public function array(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $catCache    = Category::all()->keyBy(fn($c) => strtolower($c->name));
        $prodCache   = Product::all()->keyBy(fn($p) => strtolower($p->name));
        $skuCache    = ProductVariant::all()->keyBy(fn($v) => strtoupper($v->sku));
        $prodSzCache = ProductVariant::all()->keyBy(fn($v) => $v->product_id . '___' . strtolower($v->size));

        $branch = Branch::where('name', $this->branchName)->first()
            ?? Branch::firstOrCreate(
                ['name' => $this->branchName],
                ['code' => 'BR-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->branchName), 0, 4))]
            );

        DB::beginTransaction();
        try {
            $headerRowIndex = $this->detectHeaderRowIndex($rows);
            if ($headerRowIndex === -1) {
                $headerRowIndex = 0;
            }

            $headerRow = $rows[$headerRowIndex];
            $wideInfo  = $this->detectWideFormat($headerRow);
            $dataRows  = array_slice($rows, $headerRowIndex + 1);

            if ($wideInfo !== null) {
                $this->processWideFormat($dataRows, $wideInfo, $branch, $prodCache, $catCache, $skuCache, $prodSzCache);
            } else {
                $colMap = $this->buildTallColumnMap($headerRow);
                $this->processTallFormat($dataRows, $colMap, $headerRowIndex, $branch, $prodCache, $catCache, $skuCache, $prodSzCache);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function normalise(string $val): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $val));
    }

    private function aliasLookup(): array
    {
        static $lookup = null;
        if ($lookup === null) {
            $lookup = [];
            foreach (self::COL_ALIASES as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    $lookup[$this->normalise($alias)] = $canonical;
                }
            }
        }
        return $lookup;
    }

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

    private function isNoiseRow(array $row): bool
    {
        $values = array_map('trim', array_map('strval', $row));
        if (empty(array_filter($values))) {
            return true;
        }
        $first = mb_strtolower($values[0] ?? '');
        if (mb_strlen($first) > 70) {
            return true;
        }
        $noiseKw = ['grand total', 'total', 'sub-total', 'subtotal', 'summary', 'page', 'date:', 'branch:', 'prepared by', 'month of', 'stock card', 'report', 'for the', '---', '==='];
        foreach ($noiseKw as $kw) {
            if (str_contains($first, $kw)) {
                return true;
            }
        }
        return false;
    }

    private function isCategoryHeaderRow(array $row): ?string
    {
        $nonEmpty = array_filter(
            array_map('trim', array_map('strval', $row)),
            fn($v) => $v !== '' && $v !== '0'
        );
        if (count($nonEmpty) === 1) {
            $val = trim((string)reset($nonEmpty));
            if (!is_numeric($val) && str_word_count($val) <= 6 && mb_strlen($val) <= 60) {
                $lower = mb_strtolower($val);
                $noiseKw = ['grand total', 'total', 'sub-total', 'subtotal', 'summary', 'page', 'date:', 'branch:', 'prepared by', 'month of', 'stock card', 'report', 'for the'];
                foreach ($noiseKw as $kw) {
                    if (str_contains($lower, $kw)) {
                        return null;
                    }
                }
                return $val;
            }
        }
        return null;
    }

    private function detectWideFormat(array $headerRow): ?array
    {
        $sizeColumns = [];
        $productCol  = null;
        $priceCol    = null;
        $lookup      = $this->aliasLookup();

        foreach ($headerRow as $colIdx => $cell) {
            $raw  = trim((string)($cell ?? ''));
            $norm = $this->normalise($raw);

            if (isset($lookup[$norm]) && $lookup[$norm] === 'product_name') {
                $productCol = $colIdx;
                continue;
            }
            if (isset($lookup[$norm]) && $lookup[$norm] === 'selling_price') {
                $priceCol = $colIdx;
                continue;
            }

            if (preg_match('/^(size\s*)?(\d{1,3})$/i', $raw, $m)) {
                $sz = (int)$m[2];
                if ($sz >= 10 && $sz <= 60) {
                    $sizeColumns[$colIdx] = (string)$sz;
                    continue;
                }
            }

            if (in_array(strtoupper($raw), ['XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL', 'XXXL'])) {
                $sizeColumns[$colIdx] = strtoupper($raw);
                continue;
            }
        }

        if (count($sizeColumns) >= 2) {
            if ($productCol === null) {
                foreach ($headerRow as $colIdx => $cell) {
                    if (!isset($sizeColumns[$colIdx])) {
                        $productCol = $colIdx;
                        break;
                    }
                }
            }
            return ['productCol' => $productCol ?? 0, 'sizeColumns' => $sizeColumns, 'priceCol' => $priceCol];
        }

        return null;
    }

    private function detectHeaderRowIndex(array $rows, int $scanLimit = 20): int
    {
        $lookup = $this->aliasLookup();
        $best   = ['index' => -1, 'score' => 0];

        $limit = min($scanLimit, count($rows));
        for ($i = 0; $i < $limit; $i++) {
            $score = 0;
            foreach ($rows[$i] as $cell) {
                $raw  = trim((string)($cell ?? ''));
                $norm = $this->normalise($raw);

                if ($norm !== '' && isset($lookup[$norm])) {
                    $score++;
                }
                if (preg_match('/^(size\s*)?\d{2,3}$/i', $raw)) {
                    $score++;
                }
                if (in_array(strtoupper($raw), ['XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL'])) {
                    $score++;
                }
            }
            if ($score >= 2 && $score > $best['score']) {
                $best = ['index' => $i, 'score' => $score];
            }
        }

        return $best['index'];
    }

    private function buildTallColumnMap(array $headerRow): array
    {
        $lookup = $this->aliasLookup();
        $map    = [];
        $used   = [];

        foreach ($headerRow as $colIdx => $cell) {
            $norm = $this->normalise((string)($cell ?? ''));
            if ($norm !== '' && isset($lookup[$norm]) && !in_array($lookup[$norm], $used)) {
                $map[$colIdx]  = $lookup[$norm];
                $used[]        = $lookup[$norm];
            }
        }
        return $map;
    }

    private function tallResolve(array $row, array $colMap, string $key): string
    {
        foreach ($colMap as $colIdx => $can) {
            if ($can === $key && array_key_exists($colIdx, $row)) {
                return trim((string)($row[$colIdx] ?? ''));
            }
        }
        return '';
    }

    private function ensureCategory(string $catName, object &$catCache): Category
    {
        $key = strtolower($catName);
        if (!isset($catCache[$key])) {
            $cat = Category::firstOrCreate(
                ['name' => $catName],
                ['description' => "Category for {$catName}"]
            );
            $catCache[$key] = $cat;
            $this->importedCategories++;
        }
        return $catCache[$key];
    }

    private function ensureProduct(
        string $prodName,
        string $skuPrefix,
        Category $category,
        object &$prodCache
    ): Product {
        $key = strtolower($prodName);
        if (!isset($prodCache[$key])) {
            $finalPrefix = $skuPrefix;
            $counter     = 2;
            while (Product::where('sku_prefix', $finalPrefix)->exists()) {
                $finalPrefix = $skuPrefix . $counter++;
            }
            $prod = Product::create([
                'name'        => $prodName,
                'sku_prefix'  => $finalPrefix,
                'category_id' => $category->id,
                'description' => "Uniform item: {$prodName}",
            ]);
            $prodCache[$key] = $prod;
            $this->importedProducts++;
        }
        return $prodCache[$key];
    }

    private function upsertVariant(
        Product $product,
        string  $size,
        string  $colour,
        string  $sku,
        float   $price,
        float   $stock,
        Branch  $branch,
        object  &$skuCache,
        object  &$prodSzCache
    ): void {
        $szKey    = $product->id . '___' . strtolower($size);
        $skuUpper = strtoupper($sku);
        $variant  = null;

        if ($skuUpper !== '' && isset($skuCache[$skuUpper])) {
            $variant = $skuCache[$skuUpper];
        } elseif (isset($prodSzCache[$szKey])) {
            $variant = $prodSzCache[$szKey];
        }

        if (!$variant) {
            if ($sku === '') {
                $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
                $baseSku   = $product->sku_prefix . '-' . $cleanSize;
                $finalSku  = $baseSku;
                $v2        = 2;
                while (isset($skuCache[$finalSku]) || ProductVariant::where('sku', $finalSku)->exists()) {
                    $finalSku = $baseSku . '-V' . $v2++;
                }
                $sku = $finalSku;
            }

            $variant = ProductVariant::create([
                'product_id'      => $product->id,
                'size'            => $size,
                'sku'             => $sku,
                'barcode'         => 'BC-' . $sku,
                'colour'          => $colour ?: 'Standard',
                'unit_of_measure' => 'Pieces',
                'selling_price'   => $price > 0 ? $price : 0.00,
                'stock_quantity'  => 0.00,
            ]);

            $skuCache[strtoupper($sku)] = $variant;
            $prodSzCache[$szKey]        = $variant;
            $this->importedVariants++;
        } else {
            if ($price > 0 && ($variant->selling_price == 0 || $variant->selling_price === null)) {
                $variant->selling_price = $price;
                $variant->save();
            }
            $this->updatedVariants++;
        }

        if ($stock > 0) {
            $variant->setStockForBranch($branch->name, $stock);
        }
    }

    private function processWideFormat(
        array   $dataRows,
        array   $wideInfo,
        Branch  $branch,
        object  &$prodCache,
        object  &$catCache,
        object  &$skuCache,
        object  &$prodSzCache
    ): void {
        $productCol  = $wideInfo['productCol'];
        $sizeColumns = $wideInfo['sizeColumns'];
        $priceCol    = $wideInfo['priceCol'];
        $skipCols    = $priceCol !== null ? [$priceCol] : [];
        $currentCategory = null;

        foreach ($dataRows as $dataIdx => $row) {
            $rowNum = $dataIdx + 2;

            if ($this->isNoiseRow($row)) {
                $this->noise++;
                continue;
            }

            $wideSubHeader = $this->detectWideFormat($row);
            if ($wideSubHeader !== null) {
                $this->noise++;
                continue;
            }

            $catHeader = $this->isCategoryHeaderRow($row);
            if ($catHeader !== null) {
                $currentCategory = $catHeader;
                $this->noise++;
                continue;
            }

            $prodName = trim((string)($row[$productCol] ?? ''));
            if ($prodName === '') {
                foreach ($row as $val) {
                    $val = trim((string)($val ?? ''));
                    if ($val !== '' && !is_numeric($val)) {
                        $prodName = $val;
                        break;
                    }
                }
            }

            if ($prodName === '') {
                $this->noise++;
                continue;
            }

            $price = 0.0;
            if ($priceCol !== null && isset($row[$priceCol])) {
                $rawPrice = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string)$row[$priceCol]));
                $price    = (float)$rawPrice;
            }

            $catName = $currentCategory ?? $this->categoryFromName($prodName);
            $category = $this->ensureCategory($catName, $catCache);
            $skuPrefix = $this->generateSkuPrefix($prodName);
            $product   = $this->ensureProduct($prodName, $skuPrefix, $category, $prodCache);

            $anyVariantCreated = false;
            foreach ($sizeColumns as $colIdx => $size) {
                if (in_array($colIdx, $skipCols)) {
                    continue;
                }

                $rawQty = trim((string)($row[$colIdx] ?? ''));
                if ($rawQty === '' || $rawQty === null) {
                    continue;
                }
                $cleanQty = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $rawQty));
                if (!is_numeric($cleanQty)) {
                    continue;
                }
                $qty = (float)$cleanQty;
                if ($qty <= 0) {
                    continue;
                }

                $this->upsertVariant(
                    $product,
                    $size,
                    'Standard',
                    '',
                    $price,
                    $qty,
                    $branch,
                    $skuCache,
                    $prodSzCache
                );
                $anyVariantCreated = true;
            }

            if (!$anyVariantCreated) {
                $this->skipped++;
            }
        }
    }

    private function processTallFormat(
        array   $dataRows,
        array   $colMap,
        int     $headerRowIndex,
        Branch  $branch,
        object  &$prodCache,
        object  &$catCache,
        object  &$skuCache,
        object  &$prodSzCache
    ): void {
        foreach ($dataRows as $dataIdx => $row) {
            $rowNum = $headerRowIndex + $dataIdx + 2;

            if ($this->isNoiseRow($row)) {
                $this->noise++;
                continue;
            }

            $catName  = $this->tallResolve($row, $colMap, 'category_name');
            $prodName = $this->tallResolve($row, $colMap, 'product_name');
            $skuPfx   = strtoupper($this->tallResolve($row, $colMap, 'sku_prefix'));
            $sku      = strtoupper($this->tallResolve($row, $colMap, 'sku'));
            $size     = $this->tallResolve($row, $colMap, 'size');
            $colour   = $this->tallResolve($row, $colMap, 'colour') ?: 'Standard';

            $rawPrice = str_replace(',', '', $this->tallResolve($row, $colMap, 'selling_price'));
            $rawStock = str_replace(',', '', $this->tallResolve($row, $colMap, 'initial_stock'));

            $price = is_numeric($rawPrice) ? (float)$rawPrice : 0.0;
            $stock = is_numeric($rawStock) ? (float)$rawStock : 0.0;

            $vals = array_values($row);
            // Only use positional fallback if column headers were completely unmapped
            if (empty($colMap)) {
                if ($catName  === '' && isset($vals[0]) && !is_numeric($vals[0])) $catName  = trim((string)$vals[0]);
                if ($prodName === '' && isset($vals[1])) $prodName = trim((string)$vals[1]);
                if ($skuPfx   === '' && isset($vals[2])) $skuPfx   = strtoupper(trim((string)$vals[2]));
                if ($size     === '' && isset($vals[3])) $size     = trim((string)$vals[3]);
                if ($colour   === 'Standard' && isset($vals[4]) && trim((string)$vals[4]) !== '') $colour = trim((string)$vals[4]);
                if ($price    === 0.0 && isset($vals[5]) && is_numeric(str_replace(',', '', (string)$vals[5]))) $price = (float)str_replace(',', '', (string)$vals[5]);
                if ($stock    === 0.0 && isset($vals[6]) && is_numeric(str_replace(',', '', (string)$vals[6]))) $stock = (float)str_replace(',', '', (string)$vals[6]);
            }

            if ($prodName === '') {
                $this->skipped++;
                $this->errors[] = "Row #{$rowNum}: Skipped — Product name is empty.";
                continue;
            }

            // Always classify category from product name if category column is missing or blank
            if ($catName === '' || strtolower($catName) === 'general uniforms') {
                $catName = $this->categoryFromName($prodName);
            }

            if ($skuPfx === '') {
                $skuPfx = $this->generateSkuPrefix($prodName);
            }

            $category = $this->ensureCategory($catName, $catCache);
            $product  = $this->ensureProduct($prodName, $skuPfx, $category, $prodCache);

            $this->upsertVariant(
                $product, $size, $colour, $sku, $price, $stock,
                $branch, $skuCache, $prodSzCache
            );
        }
    }
}
