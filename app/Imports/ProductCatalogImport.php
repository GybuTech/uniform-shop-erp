<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductCatalogImport implements ToCollection, WithHeadingRow
{
    public int $importedProducts = 0;
    public int $importedVariants = 0;
    public int $importedCategories = 0;
    public int $updatedVariants = 0;
    public int $skipped = 0;
    public array $errors = [];

    protected string $branchName;

    public function __construct(string $branchName = 'Main Shop')
    {
        $this->branchName = trim($branchName) ?: 'Main Shop';
    }

    /**
     * Process each row of the spreadsheet (.xlsx, .xls, .csv).
     */
    public function collection(Collection $rows): void
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if ($rows->isEmpty()) {
            $this->errors[] = 'Uploaded spreadsheet contains no data rows.';
            return;
        }

        // Pre-cache existing records in memory to eliminate N+1 queries
        $categoriesByName = Category::all()->keyBy(fn($c) => strtolower(trim($c->name)));
        $productsByName   = Product::all()->keyBy(fn($p) => strtolower(trim($p->name)));
        $variantsBySku    = ProductVariant::all()->keyBy(fn($v) => strtoupper(trim($v->sku)));
        $variantsByProdSz = ProductVariant::all()->keyBy(fn($v) => $v->product_id . '___' . strtolower(trim($v->size)));

        // Ensure the target branch exists
        $branch = Branch::where('name', $this->branchName)->first()
            ?? Branch::firstOrCreate(['name' => $this->branchName], ['code' => 'BR-' . rand(10, 99)]);

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $rawRow) {
                $rowNum = $index + 2; // Row 1 is header

                $row = is_array($rawRow) ? $rawRow : $rawRow->toArray();
                if (empty(array_filter($row, fn($val) => $val !== null && trim((string)$val) !== ''))) {
                    continue; // Skip entirely blank row
                }

                // Normalize array keys: lowercase alphanumeric
                $norm = [];
                foreach ($row as $k => $v) {
                    $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$k)));
                    $norm[$cleanKey] = is_string($v) ? trim($v) : $v;
                }

                // 1. Resolve Product Name
                $prodName = trim((string)(
                    $norm['product_name']
                    ?? $norm['product']
                    ?? $norm['item_name']
                    ?? $norm['item']
                    ?? $norm['uniform']
                    ?? $norm['name']
                    ?? $norm['description']
                    ?? $norm['particulars']
                    ?? ''
                ));

                // 2. Resolve Size
                $size = trim((string)(
                    $norm['size']
                    ?? $norm['sizes']
                    ?? $norm['variant_size']
                    ?? $norm['age']
                    ?? ''
                ));

                // 3. Resolve SKU / SKU Prefix
                $sku = strtoupper(trim((string)(
                    $norm['sku']
                    ?? $norm['item_code']
                    ?? $norm['code']
                    ?? $norm['barcode']
                    ?? ''
                )));
                $skuPrefix = strtoupper(trim((string)($norm['sku_prefix'] ?? '')));

                // If product name is empty but SKU exists, look up existing variant
                if (empty($prodName) && !empty($sku) && isset($variantsBySku[$sku])) {
                    $existingVar = $variantsBySku[$sku];
                    $prodName = $existingVar->product?->name ?? '';
                    if (empty($size)) {
                        $size = $existingVar->size ?? 'Standard';
                    }
                }

                // If still no product name, skip
                if (empty($prodName)) {
                    $this->skipped++;
                    $this->errors[] = "Row #{$rowNum}: Skipped — Missing product name.";
                    continue;
                }

                // Fallback for size if missing
                if (empty($size)) {
                    // Try to extract size from product name (e.g. "Sweater Size 26" or "Polo 8yrs")
                    if (preg_match('/\b(size|sz|age|yrs)?\s*([0-9]{2,3}|[0-9]{1,2}yrs|[SMLXL]{1,3})\b/i', $prodName, $matches)) {
                        $size = $matches[2];
                    } else {
                        $size = 'Standard';
                    }
                }

                // 4. Resolve Category
                $catName = trim((string)(
                    $norm['category_name']
                    ?? $norm['category']
                    ?? $norm['cat']
                    ?? $norm['type']
                    ?? $norm['school']
                    ?? $norm['department']
                    ?? ''
                ));

                if (empty($catName)) {
                    $lowerProd = strtolower($prodName);
                    if (str_contains($lowerProd, 'sweater')) {
                        $catName = 'School Sweaters';
                    } elseif (str_contains($lowerProd, 'tracksuit')) {
                        $catName = 'Tracksuits';
                    } elseif (str_contains($lowerProd, 'shirt') || str_contains($lowerProd, 'blouse')) {
                        $catName = 'Shirts & Blouses';
                    } elseif (str_contains($lowerProd, 'trouser') || str_contains($lowerProd, 'short') || str_contains($lowerProd, 'skirt')) {
                        $catName = 'Trousers & Shorts';
                    } elseif (str_contains($lowerProd, 't-shirt') || str_contains($lowerProd, 'polo')) {
                        $catName = 'T-Shirts & Polo';
                    } else {
                        $catName = 'General Uniforms';
                    }
                }

                // 5. Resolve Colour
                $colour = trim((string)($norm['colour'] ?? $norm['color'] ?? 'Standard'));
                if (empty($colour)) {
                    $colour = 'Standard';
                }

                // 6. Resolve Price
                $price = (float)($norm['selling_price']
                    ?? $norm['price']
                    ?? $norm['unit_price']
                    ?? $norm['rate']
                    ?? $norm['retail_price']
                    ?? 0);

                // 7. Resolve Initial Stock / Quantity
                $stock = (float)($norm['initial_stock']
                    ?? $norm['stock']
                    ?? $norm['quantity']
                    ?? $norm['qty']
                    ?? $norm['count']
                    ?? $norm['balance']
                    ?? $norm['pieces']
                    ?? $norm['units']
                    ?? 0);

                // A. Match or create Category
                $catKey = strtolower($catName);
                if (!isset($categoriesByName[$catKey])) {
                    $category = Category::firstOrCreate(
                        ['name' => $catName],
                        ['description' => "Category for {$catName}"]
                    );
                    $categoriesByName[$catKey] = $category;
                    $this->importedCategories++;
                }
                $category = $categoriesByName[$catKey];

                // B. Match or create Product
                $prodKey = strtolower($prodName);
                if (!isset($productsByName[$prodKey])) {
                    if (empty($skuPrefix)) {
                        $skuPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prodName), 0, 4));
                        if (strlen($skuPrefix) < 2) {
                            $skuPrefix = 'PRD';
                        }
                    }

                    // Ensure unique sku_prefix
                    $effectivePrefix = $skuPrefix;
                    $counter = 2;
                    while (Product::where('sku_prefix', $effectivePrefix)->exists()) {
                        $effectivePrefix = "{$skuPrefix}{$counter}";
                        $counter++;
                    }

                    $product = Product::create([
                        'name'        => $prodName,
                        'sku_prefix'  => $effectivePrefix,
                        'category_id' => $category->id,
                        'description' => "Uniform item: {$prodName}",
                    ]);
                    $productsByName[$prodKey] = $product;
                    $this->importedProducts++;
                }
                $product = $productsByName[$prodKey];

                // C. Match or create ProductVariant
                $prodSzKey = $product->id . '___' . strtolower($size);
                $variant = null;

                if (!empty($sku) && isset($variantsBySku[$sku])) {
                    $variant = $variantsBySku[$sku];
                } elseif (isset($variantsByProdSz[$prodSzKey])) {
                    $variant = $variantsByProdSz[$prodSzKey];
                }

                $isNewVariant = false;
                if (!$variant) {
                    $isNewVariant = true;

                    // Generate variant SKU
                    if (empty($sku)) {
                        $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
                        $baseSku = "{$product->sku_prefix}-{$cleanSize}";
                        if (!empty($colour) && strtolower($colour) !== 'standard') {
                            $baseSku .= '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $colour), 0, 3));
                        }

                        $finalSku = $baseSku;
                        $vCounter = 2;
                        while (isset($variantsBySku[$finalSku]) || ProductVariant::where('sku', $finalSku)->exists()) {
                            $finalSku = "{$baseSku}-V{$vCounter}";
                            $vCounter++;
                        }
                        $sku = $finalSku;
                    }

                    $variant = new ProductVariant();
                    $variant->product_id      = $product->id;
                    $variant->size            = $size;
                    $variant->sku             = $sku;
                    $variant->barcode         = 'BC-' . $sku;
                    $variant->colour          = $colour;
                    $variant->unit_of_measure = 'Pieces';
                    $variant->stock_quantity  = 0.00;
                    $this->importedVariants++;
                } else {
                    $this->updatedVariants++;
                }

                // Update selling price if specified
                if ($price > 0) {
                    $variant->selling_price = $price;
                } elseif ($variant->selling_price === null) {
                    $variant->selling_price = 0.00;
                }

                if (!empty($colour) && $colour !== 'Standard') {
                    $variant->colour = $colour;
                }

                $variant->save();

                // Update memory cache
                $variantsBySku[strtoupper($variant->sku)] = $variant;
                $variantsByProdSz[$prodSzKey] = $variant;

                // D. Set stock quantity for the branch if present
                if ($stock > 0) {
                    $variant->setStockForBranch($branch->name, $stock);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
