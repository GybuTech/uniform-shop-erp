<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $categoryId = $request->input('category_id');

        $query = Product::with(['category', 'variants'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku_prefix', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%")
                         ->orWhere('size', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%")
                         ->orWhere('colour', 'like', "%{$search}%");
                  });
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'search', 'categories', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Sanitize sku_prefix format
        if ($request->filled('sku_prefix')) {
            $request->merge([
                'sku_prefix' => strtoupper(Str::slug(substr($request->sku_prefix, 0, 50))),
            ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')->whereNull('deleted_at'),
            ],
            'sku_prefix' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku_prefix')->whereNull('deleted_at'),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Sanitize sku_prefix format
        if ($request->filled('sku_prefix')) {
            $request->merge([
                'sku_prefix' => strtoupper(Str::slug(substr($request->sku_prefix, 0, 50))),
            ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')->ignore($product->id)->whereNull('deleted_at'),
            ],
            'sku_prefix' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku_prefix')->ignore($product->id)->whereNull('deleted_at'),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($product) {
                $variantIds = $product->variants()->withTrashed()->pluck('id');

                $hasTransactions = SaleItem::whereIn('product_variant_id', $variantIds)->exists()
                    || PurchaseItem::whereIn('product_variant_id', $variantIds)->exists()
                    || StockTransferItem::whereIn('product_variant_id', $variantIds)->exists()
                    || StockAdjustmentItem::whereIn('product_variant_id', $variantIds)->exists()
                    || StockEntryItem::whereIn('product_variant_id', $variantIds)->exists();

                if (!$hasTransactions) {
                    // Safe to completely permanently delete (test / unused product)
                    $product->variants()->forceDelete();
                    $product->forceDelete();
                } else {
                    // Release the original unique name and sku_prefix for future reuse
                    $product->update([
                        'sku_prefix' => $product->sku_prefix . '-ARCHIVED-' . $product->id,
                        'name'       => $product->name . ' (Archived)',
                    ]);
                    $product->variants()->delete();
                    $product->delete();
                }
            });

            return redirect()->route('products.index')
                ->with('success', 'Product and its variants removed successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('products.index')
                ->with('error', 'Unable to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Download pre-formatted CSV template for bulk importing real products, sizes & prices.
     */
    public function downloadCatalogTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tshaprint_real_product_catalog_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Column Header
            fputcsv($out, ['category_name', 'product_name', 'sku_prefix', 'size', 'colour', 'selling_price', 'initial_stock']);

            // Sample rows to guide the client
            fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '22', 'Standard', 500.00, 0]);
            fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '24', 'Standard', 500.00, 0]);
            fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '26', 'Standard', 550.00, 0]);
            fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '26', 'Navy Blue', 900.00, 0]);
            fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '28', 'Navy Blue', 900.00, 0]);
            fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '30', 'Navy Blue', 1000.00, 0]);

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Bulk import real products, categories, size variants, and prices from spreadsheet.
     */
    public function importCatalog(Request $request)
    {
        $request->validate([
            'catalog_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
            'branch_name'  => ['nullable', 'string'],
        ]);

        $file = $request->file('catalog_file');
        $branchName = $request->input('branch_name') ?: 'Main Shop';

        $importedProducts = 0;
        $importedVariants = 0;
        $importedCategories = 0;

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Unable to open uploaded spreadsheet.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'Uploaded file is empty.');
        }

        $header = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $h))), $header);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;
                $data = array_combine($header, array_pad($row, count($header), ''));

                $catName   = trim($data['category_name'] ?? $data['category'] ?? 'General Uniforms');
                $prodName  = trim($data['product_name'] ?? $data['product'] ?? '');
                $skuPrefix = strtoupper(trim($data['sku_prefix'] ?? ''));
                $size      = trim($data['size'] ?? '');
                $colour    = trim($data['colour'] ?? $data['color'] ?? 'Standard');
                $price     = (float) ($data['selling_price'] ?? $data['price'] ?? 0);
                $stock     = (float) ($data['initial_stock'] ?? $data['stock'] ?? 0);

                if (empty($prodName) || empty($size)) {
                    continue;
                }

                // 1. Resolve Category
                $category = Category::firstOrCreate(
                    ['name' => $catName],
                    ['description' => "Category for {$catName}"]
                );
                if ($category->wasRecentlyCreated) {
                    $importedCategories++;
                }

                // 2. Resolve Product
                if (empty($skuPrefix)) {
                    $skuPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prodName), 0, 4));
                }

                $product = Product::where('name', $prodName)->first();
                if (!$product) {
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
                    $importedProducts++;
                }

                // 3. Resolve Product Variant
                $variantSku = "{$product->sku_prefix}-" . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
                if (!empty($colour) && strtolower($colour) !== 'standard') {
                    $variantSku .= '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $colour), 0, 3));
                }

                $variant = ProductVariant::firstOrNew([
                    'product_id' => $product->id,
                    'size'       => $size,
                ]);

                if (!$variant->exists) {
                    $vCounter = 2;
                    $finalSku = $variantSku;
                    while (ProductVariant::where('sku', $finalSku)->exists()) {
                        $finalSku = "{$variantSku}-V{$vCounter}";
                        $vCounter++;
                    }
                    $variant->sku = $finalSku;
                }

                $variant->barcode         = 'BC-' . $variant->sku;
                $variant->colour          = $colour ?: 'Standard';
                $variant->unit_of_measure = 'Pieces';
                $variant->selling_price   = $price;
                $variant->stock_quantity  = 0.00;
                $variant->save();
                $importedVariants++;

                // 4. Initial stock if specified
                if ($stock > 0) {
                    $variant->adjustStockForBranch($branchName, $stock);
                }
            }

            DB::commit();
            fclose($handle);

            return redirect()->route('products.index')
                ->with('success', "Catalog imported successfully! Added {$importedProducts} product(s), {$importedVariants} variant size(s) across {$importedCategories} category(ies).");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('products.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Purge all test products, variants, selling prices, and dependent test transactions.
     * Keeps Users, Roles, Branches, and Business Settings 100% intact.
     */
    public function purgeTestCatalog(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['owner', 'admin', 'system_administrator', 'manager'])) {
            abort(403, 'Unauthorized. Only an Owner or System Administrator can purge catalog data.');
        }

        $purgeCategories = $request->boolean('purge_categories', false);

        DB::transaction(function () use ($purgeCategories) {
            // 1. Wipe dependent stock and transaction records linked to variants
            BranchStock::query()->delete();
            StockEntryItem::query()->delete();
            StockEntry::query()->delete();
            StockAdjustmentItem::query()->delete();
            StockAdjustment::query()->delete();
            StockTransferItem::query()->delete();
            StockTransfer::query()->delete();
            PurchaseItem::query()->delete();
            Purchase::query()->delete();
            SaleItem::query()->delete();
            Sale::query()->delete();

            // 2. Wipe product variants (force delete to bypass soft deletes)
            ProductVariant::withTrashed()->forceDelete();

            // 3. Wipe products (force delete to bypass soft deletes)
            Product::withTrashed()->forceDelete();

            // 4. Optionally wipe categories if requested
            if ($purgeCategories) {
                Category::query()->delete();
            }
        });

        $msg = $purgeCategories
            ? 'All test products, variants, selling prices, categories, and test transactions have been completely purged! Your database is 100% clean and ready for real catalog entry.'
            : 'All test products, variants, selling prices, and test transactions have been purged! Categories were preserved so you can immediately add your real products.';

        return redirect()->route('products.index')->with('success', $msg);
    }
}