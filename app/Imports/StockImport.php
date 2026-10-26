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

    public function __construct(?int $userId = null, ?string $customNotes = null)
    {
        $this->userId      = $userId;
        $this->customNotes = $customNotes;
    }

    /**
     * Process the collection of rows.
     * Expected columns: sku, quantity (or product_name, size, quantity)
     */
    public function collection(Collection $rows)
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

            if ($quantity <= 0) {
                $this->skipped++;
                $this->errors[] = "Row #{$rowNum}: Skipped — Quantity is 0 or invalid.";
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
                $this->skipped++;
                $label = !empty($sku) ? "SKU '{$sku}'" : "Product '{$prodName}' Size '{$size}'";
                $this->errors[] = "Row #{$rowNum}: Uniform variant not found for {$label}.";
                continue;
            }

            $entry->items()->create([
                'product_variant_id' => $variant->id,
                'quantity'           => $quantity,
            ]);

            $variant->increment('stock_quantity', $quantity);
            $this->imported++;
        }
    }
}
