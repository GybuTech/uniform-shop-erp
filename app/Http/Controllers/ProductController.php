<?php

namespace App\Http\Controllers;

use App\Imports\ProductCatalogImport;
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
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $categoryId = $request->input('category_id');

        $activeBranch = \App\Services\BranchContext::getActiveBranch();
        $isAllBranches = \App\Services\BranchContext::isAllBranches();

        $query = Product::with(['category', 'variants.branchStocks'])->latest();

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

        $perPageInput = $request->input('per_page', 25);
        $perPage = ($perPageInput === 'all') ? 1000 : max(10, min(500, (int)$perPageInput));
        $products = $query->paginate($perPage)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        if ($branches->isEmpty()) {
            $branches = Branch::orderBy('name')->get();
        }

        return view('products.index', compact(
            'products',
            'search',
            'categories',
            'categoryId',
            'branches',
            'perPageInput',
            'activeBranch',
            'isAllBranches'
        ));
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
            'is_service'  => ['nullable', 'boolean'],
        ]);

        $validated['is_service'] = $request->boolean('is_service');

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
            'is_service'  => ['nullable', 'boolean'],
        ]);

        $validated['is_service'] = $request->boolean('is_service');

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
     * Download CSV template for bulk importing/exporting real products, sizes, prices, and stock.
     * If products already exist in the database, exports ALL available catalog items.
     * If the catalog is empty, outputs pre-formatted guidance sample rows.
     */
    public function downloadCatalogTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tshaprint_product_catalog_export.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Column Header
            fputcsv($out, ['category_name', 'product_name', 'sku_prefix', 'size', 'colour', 'selling_price', 'initial_stock']);

            // Export all variants currently available in the database
            $variants = ProductVariant::with(['product.category', 'branchStocks'])
                ->whereHas('product')
                ->orderBy('sku')
                ->get();

            if ($variants->isNotEmpty()) {
                foreach ($variants as $v) {
                    fputcsv($out, [
                        $v->product?->category?->name ?? 'General Uniforms',
                        $v->product?->name ?? 'Uniform Item',
                        $v->product?->sku_prefix ?? 'PRD',
                        $v->size ?? 'Standard',
                        $v->colour ?? 'Standard',
                        $v->selling_price ?? 0.00,
                        $v->stock_quantity ?? 0,
                    ]);
                }
            } else {
                // Baseline template sample rows (shown only if database is completely blank)
                fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '22', 'Standard', 500.00, 0]);
                fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '24', 'Standard', 500.00, 0]);
                fputcsv($out, ['School Sweaters', 'Accurate Sweater', 'ACC-SW', '26', 'Standard', 550.00, 0]);
                fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '26', 'Navy Blue', 900.00, 0]);
                fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '28', 'Navy Blue', 900.00, 0]);
                fputcsv($out, ['Tracksuits', 'Kayole Tracksuit', 'KAY-TRK', '30', 'Navy Blue', 1000.00, 0]);
            }

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Bulk import real products, categories, size variants, prices, and stock from Excel or CSV spreadsheet.
     */
    public function importCatalog(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'catalog_file' => ['required', 'file', 'max:25600'],
            'branch_name'  => ['nullable', 'string'],
        ], [
            'catalog_file.required' => 'Please select a spreadsheet file (.xlsx, .xls, or .csv) to upload.',
        ]);

        $file = $request->file('catalog_file');
        $ext  = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return redirect()->back()->with('error', 'Unsupported file type. Please upload an Excel (.xlsx, .xls) or CSV (.csv) spreadsheet.');
        }

        $branchName = $request->input('branch_name') ?: 'Main Shop';

        try {
            $importer = new ProductCatalogImport($branchName);
            $importer->importFile($file->getRealPath(), $ext);

            return redirect()->route('products.index')
                ->with('catalog_import_variants',   $importer->importedVariants)
                ->with('catalog_import_updated',    $importer->updatedVariants)
                ->with('catalog_import_products',   $importer->importedProducts)
                ->with('catalog_import_categories', $importer->importedCategories)
                ->with('catalog_import_skipped',    $importer->skipped)
                ->with('catalog_import_branch',     $branchName)
                ->with('catalog_import_errors',     $importer->errors);
        } catch (\Throwable $e) {
            return redirect()->route('products.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
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