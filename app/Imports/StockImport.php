<?php

namespace App\Imports;

use App\Models\ProductVariant;
use App\Models\StockEntry;
use App\Services\SystemTime;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockImport implements ToCollection, WithHeadingRow
{
    public int   $imported = 0;
    public int   $skipped  = 0;
    public array $errors   = [];
    private ?int $userId;
    private ?string $customNotes;
    private string $mode;
    private ?string $branchName;

    public function __construct(?int $userId = null, ?string $customNotes = null, string $mode = 'replace', ?string $branchName = null)
    {
        $this->userId      = $userId;
        $this->customNotes = $customNotes;
        $this->mode        = in_array($mode, ['replace', 'add']) ? $mode : 'replace';
        $this->branchName  = $branchName ? trim($branchName) : null;
    }

    /**
     * Process the collection of rows.
     * Expected columns: sku, quantity (or product_name, size, quantity, selling_price)
     */
    public function collection(Collection $rows): void
    {
        $now   = SystemTime::now();
        $notes = $this->customNotes ?: ('Spreadsheet Opening Stock Intake on ' . $now->format('d M Y H:i'));

        $entry = StockEntry::create([
            'user_id'      => $this->userId,
            'reference_no' => 'IMPORT-' . $now->format('YmdHis'),
            'notes'        => $notes,
        ]);

        foreach ($rows as $index => $row) {
            $rowNum   = $index + 2; // Row 1 is the header
            $sku      = strtoupper(trim($row['sku'] ?? ''));
            $prodName = trim($row['product_name'] ?? $row['product'] ?? '');
            $size     = trim($row['size'] ?? '');
            $quantity = (int) ($row['quantity'] ?? $row['qty'] ?? $row['stock'] ?? 0);

            if ($quantity < 0) {
                $this->skipped++;
                $this->errors[] = "Row #{$rowNum}: Skipped — Quantity cannot be negative.";
                continue;
            }

            // 1. Match by SKU
            $variant = null;
            if (!empty($sku)) {
                $variant = ProductVariant::where('sku', $sku)->first();
            }

            // 2. Fallback: Match by Product Name + Size
            if (!$variant && !empty($prodName) && !empty($size)) {
                $variant = ProductVariant::whereHas('product', function ($q) use ($prodName) {
                    $q->where('name', 'like', "%{$prodName}%");
                })->where('size', $size)->first();
            }

            if (!$variant) {
                if (!empty($prodName) && !empty($size)) {
                    $cat = \App\Models\Category::firstOrCreate(['name' => 'General Uniforms']);
                    $skuPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prodName), 0, 4)) ?: 'PRD';
                    $product = \App\Models\Product::firstOrCreate(
                        ['name' => $prodName],
                        ['sku_prefix' => $skuPrefix, 'category_id' => $cat->id]
                    );

                    $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
                    $genSku = !empty($sku) ? $sku : "{$product->sku_prefix}-{$cleanSize}";
                    $variant = ProductVariant::create([
                        'product_id'      => $product->id,
                        'size'            => $size,
                        'sku'             => $genSku,
                        'barcode'         => 'BC-' . $genSku,
                        'colour'          => 'Standard',
                        'unit_of_measure' => 'Pieces',
                        'selling_price'   => isset($row['selling_price']) && is_numeric($row['selling_price']) ? (float)$row['selling_price'] : 0.00,
                        'stock_quantity'  => 0.00,
                    ]);
                } else {
                    $this->skipped++;
                    $label = !empty($sku) ? "SKU '{$sku}'" : "Product '{$prodName}' Size '{$size}'";
                    $this->errors[] = "Row #{$rowNum}: Uniform variant not found for {$label}.";
                    continue;
                }
            }

            $entry->items()->create([
                'product_variant_id' => $variant->id,
                'quantity'           => $quantity,
            ]);

            // Update stock according to selected mode & branch
            if (!empty($this->branchName)) {
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

            // Optional selling price sync from spreadsheet
            $sellingPrice = isset($row['selling_price']) && is_numeric($row['selling_price']) && (float)$row['selling_price'] > 0
                ? (float)$row['selling_price']
                : null;
            if ($sellingPrice !== null) {
                $variant->update(['selling_price' => $sellingPrice]);
            }

            $this->imported++;
        }
    }
}
