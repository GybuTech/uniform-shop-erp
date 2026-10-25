<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(15);
        return view('products.index', compact('products'));
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
            'name'        => ['required', 'string', 'max:150', 'unique:products,name'],
            'sku_prefix'  => ['required', 'string', 'max:50', 'unique:products,sku_prefix'],
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
            'name'        => ['required', 'string', 'max:150', 'unique:products,name,' . $product->id],
            'sku_prefix'  => ['required', 'string', 'max:50', 'unique:products,sku_prefix,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->variants()->delete();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product and its variants removed successfully.');
    }
}