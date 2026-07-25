<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->latest()->paginate(10);
        return view('variants.index', compact('product', 'variants'));
    }

    public function create(Product $product)
    {
        return view('variants.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
    'size'          => ['required', 'string', 'max:20'],
    'colour'        => ['required', 'string', 'max:50'],
    'selling_price' => ['required', 'numeric', 'min:0'],
]);

$sku = strtoupper($product->sku_prefix . '-' . $validated['size'] . '-' . $validated['colour']);
$sku = str_replace(' ', '-', $sku);

$exists = ProductVariant::where('sku', $sku)->exists();
if ($exists) {
    return back()->withErrors(['size' => 'A variant with this size and colour already exists.'])->withInput();
}

$barcode = strtoupper('BC-' . $product->sku_prefix . '-' . str_replace(' ', '-', $validated['size']) . '-' . str_replace(' ', '-', $validated['colour']));

$product->variants()->create([
    'sku'            => $sku,
    'barcode'        => $barcode,
    'size'           => $validated['size'],
    'colour'         => $validated['colour'],
    'selling_price'  => $validated['selling_price'],
    'stock_quantity' => 0,
]);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    public function edit(Product $product, ProductVariant $variant)
    {
        return view('variants.edit', compact('product', 'variant'));
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
    'size'          => ['required', 'string', 'max:20'],
    'colour'        => ['required', 'string', 'max:50'],
    'selling_price' => ['required', 'numeric', 'min:0'],
]);

$sku = strtoupper($product->sku_prefix . '-' . $validated['size'] . '-' . $validated['colour']);
$sku = str_replace(' ', '-', $sku);

$barcode = strtoupper('BC-' . $product->sku_prefix . '-' . str_replace(' ', '-', $validated['size']) . '-' . str_replace(' ', '-', $validated['colour']));

$variant->update([
    'sku'           => $sku,
    'barcode'       => $barcode,
    'size'          => $validated['size'],
    'colour'        => $validated['colour'],
    'selling_price' => $validated['selling_price'],
]);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant updated successfully.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }
}