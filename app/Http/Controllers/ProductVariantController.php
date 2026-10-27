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
        $variants = $product->variants()->latest()->paginate(25);
        $availableSizes = $product->variants()->distinct()->pluck('size')->toArray();
        return view('variants.index', compact('product', 'variants', 'availableSizes'));
    }

    public function create(Product $product)
    {
        return view('variants.create', compact('product'));
    }

    /**
     * Generate a concise SKU abbreviation for a variant.
     * Keeps SKUs clean, human-readable, and prevents repetition.
     * e.g. Product "Accurate Sweater" (ACC-SW), Size 24 -> "ACC-SW-24"
     * If colour is distinct (e.g. "Navy Blue"), appends "-NB" -> "ACC-SW-24-NB"
     */
    private function generateVariantSku(Product $product, string $size, string $colour): string
    {
        $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
        $normColour = strtolower(preg_replace('/[^a-z0-9]/', '', $colour));
        $normProd   = strtolower(preg_replace('/[^a-z0-9]/', '', $product->name));

        $colourSuffix = '';
        if (!empty($normColour) && $normColour !== $normProd && !str_contains($normProd, $normColour)) {
            $words = preg_split('/\s+/', trim(preg_replace('/[^A-Za-z0-9\s]/', ' ', $colour)));
            $abbr  = implode('', array_map(fn($w) => strtoupper(substr($w, 0, 1)), $words));
            $colourSuffix = '-' . substr($abbr, 0, 4);
        }

        $baseSku = strtoupper($product->sku_prefix . '-' . $cleanSize . $colourSuffix);
        $sku = $baseSku;
        $counter = 2;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$baseSku}-V{$counter}";
            $counter++;
        }

        return $sku;
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'size'          => ['required', 'string', 'max:20'],
            'colour'        => ['required', 'string', 'max:50'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $sku = $this->generateVariantSku($product, $validated['size'], $validated['colour']);
        $barcode = 'BC-' . $sku;

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
        // Sizes from 22 up to 38 (even numbers) for primary & secondary school uniforms
        $defaultSizes = ['22', '24', '26', '28', '30', '32', '34', '36', '38'];

        // Preset T-Sharprint Solutions uniform colours & stripes
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
            'Brown Plain',
            'Green Plain',
        ];

        return view('variants.bulk_create', compact('product', 'defaultSizes', 'presetColours'));
    }

    public function bulkStore(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sizes'                   => ['required', 'array', 'min:1'],
            'selected_preset_colours' => ['nullable', 'array'],
            'custom_colours'          => ['nullable', 'string'],
            'selling_price'           => ['required', 'numeric', 'min:0'],
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
                    $cleanSize   = trim($size);
                    $cleanColour = trim($colour);

                    $sku     = $this->generateVariantSku($product, $cleanSize, $cleanColour);
                    $barcode = 'BC-' . $sku;

                    if (ProductVariant::where('product_id', $product->id)->where('size', $cleanSize)->where('colour', $cleanColour)->exists()) {
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

    public function batchPriceUpdate(Request $request, Product $product)
    {
        $request->validate([
            'sizes'         => ['nullable', 'array'],
            'variant_ids'   => ['nullable', 'array'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (empty($request->sizes) && empty($request->variant_ids)) {
            return back()->withErrors(['price_error' => 'Please select at least one size range or variant to update prices.']);
        }

        $query = $product->variants();

        if ($request->filled('variant_ids')) {
            $query->whereIn('id', $request->variant_ids);
        } elseif ($request->filled('sizes')) {
            $query->whereIn('size', $request->sizes);
        }

        $updatedCount = $query->update(['selling_price' => $request->selling_price]);

        return redirect()->route('products.variants.index', $product)
            ->with('success', "Successfully updated selling price for {$updatedCount} variant(s) to KES " . number_format($request->selling_price, 2));
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

        $colourCode = $this->abbreviateColour($validated['colour']);
        $sku        = strtoupper($product->sku_prefix . '-' . str_replace(' ', '-', $validated['size']) . '-' . $colourCode);
        $barcode    = 'BC-' . $sku;

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
        try {
            $variant->delete();

            return redirect()->route('products.variants.index', $product)
                ->with('success', 'Variant deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('products.variants.index', $product)
                ->with('error', 'Unable to delete variant: ' . $e->getMessage());
        }
    }

    public function priceManager(Request $request)
    {
        $categories = \App\Models\Category::withCount('products')->orderBy('name')->get();
        $products = Product::with('category')->orderBy('name')->get();

        // Distinct sizes for dropdowns/matrix
        $distinctSizes = ProductVariant::distinct()
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->orderByRaw('CAST(size AS UNSIGNED), size')
            ->pluck('size');

        if ($distinctSizes->isEmpty()) {
            $distinctSizes = collect(['22', '24', '26', '28', '30', '32', '34', '36', '38']);
        }

        $matrixSizes = ['22', '24', '26', '28', '30', '32', '34', '36', '38'];

        // Search and filters
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $productId = $request->input('product_id');
        $size = $request->input('size');
        $filterType = $request->input('filter_type', 'all');

        $query = ProductVariant::with('product.category')->latest();

        // 1. Dedicated Filter Type: Sweaters with Badge, Plain Sweaters, Tracksuits, etc.
        if ($filterType === 'sweater_badge') {
            $query->where(function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where('name', 'like', '%badge%')
                       ->orWhere('sku_prefix', 'like', '%badge%');
                })
                ->orWhere('colour', 'like', '%badge%')
                ->orWhere('sku', 'like', '%badge%');
            });
        } elseif ($filterType === 'sweater_plain') {
            $query->whereHas('product', function ($pq) {
                $pq->where(function ($sub) {
                    $sub->where('name', 'like', '%sweater%')
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                })
                ->where('name', 'not like', '%badge%');
            })
            ->where('colour', 'not like', '%badge%')
            ->where('sku', 'not like', '%badge%');
        } elseif ($filterType === 'sweaters') {
            $query->whereHas('product', function ($pq) {
                $pq->where('name', 'like', '%sweater%')
                   ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
            });
        } elseif ($filterType === 'tracksuits') {
            $query->whereHas('product', function ($pq) {
                $pq->where('name', 'like', '%tracksuit%')
                   ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%tracksuit%'));
            });
        }

        if ($categoryId) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($size) {
            $query->where('size', $size);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('colour', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $variants = $query->paginate(25)->withQueryString();

        return view('products.prices', compact(
            'categories',
            'products',
            'distinctSizes',
            'matrixSizes',
            'variants',
            'search',
            'categoryId',
            'productId',
            'size',
            'filterType'
        ));
    }

    public function updateBySize(Request $request)
    {
        $validated = $request->validate([
            'target_type'   => ['nullable', 'string'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'product_id'    => ['nullable', 'exists:products,id'],
            'size'          => ['required', 'string'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $targetType = $request->input('target_type', 'all');
        $size = $validated['size'];
        $price = $validated['selling_price'];

        $query = ProductVariant::where('size', $size);
        $targetName = "all products";

        if ($targetType === 'sweater_with_badge') {
            $query->where(function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where(function ($sub) {
                        $sub->where('name', 'like', '%sweater%')
                            ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                    })->where(function ($sub) {
                        $sub->where('name', 'like', '%badge%')
                            ->orWhere('sku_prefix', 'like', '%badge%');
                    });
                })
                ->orWhere(function ($sub) {
                    $sub->where(function ($sc) {
                        $sc->where('colour', 'like', '%badge%')
                           ->orWhere('sku', 'like', '%badge%');
                    })->whereHas('product', function ($pq) {
                        $pq->where('name', 'like', '%sweater%')
                           ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                    });
                });
            });
            $targetName = "Sweaters with Badge";
        } elseif ($targetType === 'sweater_plain') {
            $query->whereHas('product', function ($pq) {
                $pq->where(function ($sub) {
                    $sub->where('name', 'like', '%sweater%')
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                })->where('name', 'not like', '%badge%');
            })
            ->where('colour', 'not like', '%badge%')
            ->where('sku', 'not like', '%badge%');
        } elseif (str_starts_with($targetType, 'category_')) {
            $catId = (int) str_replace('category_', '', $targetType);
            $query->whereHas('product', fn($q) => $q->where('category_id', $catId));
            $c = \App\Models\Category::find($catId);
            $targetName = $c ? "category '{$c->name}'" : 'selected category';
        } elseif (!empty($validated['product_id'])) {
            $query->where('product_id', $validated['product_id']);
            $p = Product::find($validated['product_id']);
            $targetName = $p ? $p->name : 'selected product';
        } elseif (!empty($validated['category_id'])) {
            $query->whereHas('product', function ($q) use ($validated) {
                $q->where('category_id', $validated['category_id']);
            });
            $c = \App\Models\Category::find($validated['category_id']);
            $targetName = $c ? "category '{$c->name}'" : 'selected category';
        } else {
            $targetName = "all uniform products";
        }

        $count = $query->update(['selling_price' => $price]);

        return back()->with('success', "Updated selling price to KES " . number_format($price, 2) . " for {$count} item(s) of Size {$size} in {$targetName}.");
    }

    public function updateCategorySizeMatrix(Request $request)
    {
        $validated = $request->validate([
            'target_type' => ['nullable', 'string'],
            'category_id' => ['nullable'],
            'matrix'      => ['required', 'array'],
            'matrix.*'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $targetType = $request->input('target_type', 'category');
        if (str_starts_with($targetType, 'cat_')) {
            $validated['category_id'] = (int) str_replace('cat_', '', $targetType);
            $targetType = 'category';
        }
        $totalUpdated = 0;
        $targetLabel = "Category";

        foreach ($validated['matrix'] as $size => $price) {
            if ($price !== null && $price !== '') {
                $q = ProductVariant::where('size', (string)$size);

                if ($targetType === 'sweater_with_badge') {
                    $q->where(function ($sub) {
                        $sub->whereHas('product', function ($pq) {
                            $pq->where(function ($s) {
                                $s->where('name', 'like', '%sweater%')
                                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                            })->where(function ($s) {
                                $s->where('name', 'like', '%badge%')
                                  ->orWhere('sku_prefix', 'like', '%badge%');
                            });
                        })
                        ->orWhere(function ($s) {
                            $s->where(function ($sc) {
                                $sc->where('colour', 'like', '%badge%')
                                   ->orWhere('sku', 'like', '%badge%');
                            })->whereHas('product', function ($pq) {
                                $pq->where('name', 'like', '%sweater%')
                                   ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                            });
                        });
                    });
                    $targetLabel = "Sweaters with Badge";
                } elseif ($targetType === 'sweater_plain') {
                    $q->whereHas('product', function ($pq) {
                        $pq->where(function ($sub) {
                            $sub->where('name', 'like', '%sweater%')
                                ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%sweater%'));
                        })->where('name', 'not like', '%badge%');
                    })
                    ->where('colour', 'not like', '%badge%')
                    ->where('sku', 'not like', '%badge%');
                    $targetLabel = "Plain Sweaters (Without Badge)";
                } elseif (!empty($validated['category_id'])) {
                    $category = \App\Models\Category::find($validated['category_id']);
                    if ($category) {
                        $q->whereHas('product', fn($pq) => $pq->where('category_id', $category->id));
                        $targetLabel = "Category '{$category->name}'";
                    }
                }

                $updated = $q->update(['selling_price' => $price]);
                $totalUpdated += $updated;
            }
        }

        return back()->with('success', "Size matrix applied for {$targetLabel}. Successfully updated prices for {$totalUpdated} variants.");
    }

    public function updateSinglePrice(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $variant->update(['selling_price' => $validated['selling_price']]);

        return back()->with('success', "Updated price for {$variant->product->name} (Size {$variant->size}, {$variant->colour}) to KES " . number_format($validated['selling_price'], 2));
    }

    public function batchUpdateGlobal(Request $request)
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['required', 'numeric', 'min:0'],
        ]);

        $updatedCount = 0;
        foreach ($validated['prices'] as $variantId => $newPrice) {
            ProductVariant::where('id', $variantId)->update(['selling_price' => $newPrice]);
            $updatedCount++;
        }

        return back()->with('success', "Updated selling price for {$updatedCount} product variant(s) successfully.");
    }
}