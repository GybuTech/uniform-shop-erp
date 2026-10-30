<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\PurchaseItem;
use App\Models\StockTransferItem;
use App\Models\StockAdjustmentItem;
use App\Models\StockEntryItem;
use Illuminate\Http\Request;
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
}