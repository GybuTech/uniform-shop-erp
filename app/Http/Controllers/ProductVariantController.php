<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->latest()->paginate(15);
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

    public function bulkCreate(Product $product)
{
    $product->load('category');
    
    $defaultSizes = ['22', '24', '26', '28', '30', '32', '34', '36', '38'];

    $categoryName = strtolower($product->category->name ?? '');

    if (str_contains($categoryName, 'sweater') || str_contains($categoryName, 'tracksuit')) {
        $presetColours = [
            'Navy Blue Plain',
            'Navy Strip White',
            'Navy Strip Sky Blue',
            'Navy Strip Red',
            'Red Plain',
            'Red Strip White',
            'Red Strip Navy',
            'Maroon Plain',
            'Maroon Strip White',
            'Sky Blue Plain',
            'Sky Blue Strip Navy',
            'Green Plain',
            'Green Strip White',
        ];
    } elseif (str_contains($categoryName, 'fleece')) {
        $presetColours = [
            'Navy Blue',
            'Red',
            'Maroon',
            'Green',
            'Sky Blue',
            'Brown',
            'Black',
            'Grey',
        ];
    } else {
        $presetColours = [
            'White',
            'Navy Blue',
            'Red',
            'Maroon',
            'Green',
            'Sky Blue',
            'Black',
            'Grey',
        ];
    }

    return view('variants.bulk_create', compact('product', 'defaultSizes', 'presetColours'));
}

    public function bulkStore(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sizes'          => ['required', 'array', 'min:1'],
            'selected_preset_colours' => ['nullable', 'array'],
            'custom_colours' => ['nullable', 'string'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $presetColours = $validated['selected_preset_colours'] ?? [];
        $customColoursText = $validated['custom_colours'] ?? '';

        $rawCustomColours = array_map('trim', explode(',', str_replace(["\r\n", "\n", "\r"], ',', $customColoursText)));
        $allColours = array_filter(array_unique(array_merge($presetColours, $rawCustomColours)));

        if (empty($allColours)) {
            return back()->withErrors(['custom_colours' => 'Please select at least one preset colour or enter a custom colour.'])->withInput();
        }

        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($product, $validated, $allColours, &$createdCount, &$skippedCount) {
            foreach ($validated['sizes'] as $size) {
                foreach ($allColours as $colour) {
                    $cleanSize = trim($size);
                    $cleanColour = trim($colour);

                    $sku = strtoupper($product->sku_prefix . '-' . str_replace(' ', '-', $cleanSize) . '-' . str_replace(' ', '-', $cleanColour));
                    $barcode = 'BC-' . $sku;

                    if (ProductVariant::where('sku', $sku)->exists()) {
                        $skippedCount++;
                        continue;
                    }

                    $product->variants()->create([
                        'sku'            => $sku,
                        'barcode'        => $barcode,
                        'size'           => $cleanSize,
                        'colour'         => $cleanColour,
                        'selling_price'  => $validated['selling_price'],
                        'stock_quantity' => 0,
                    ]);

                    $createdCount++;
                }
            }
        });

        $message = "Successfully generated {$createdCount} new uniform variants.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} existing variants were skipped to avoid duplicates).";
        }

        return redirect()->route('products.variants.index', $product)
            ->with('success', $message);
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