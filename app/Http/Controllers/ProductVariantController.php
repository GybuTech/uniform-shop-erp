<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $search = trim($request->input('search', ''));
        $sizeFilter = $request->input('size');

        $query = $product->variants()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhere('colour', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%")
                  ->orWhere('unit_of_measure', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($sizeFilter) {
            $query->where('size', $sizeFilter);
        }

        $activeBranch = \App\Services\BranchContext::getActiveBranch();
        $isAllBranches = \App\Services\BranchContext::isAllBranches();

        $variants = $query->paginate(25)->withQueryString();
        $availableSizes = $product->variants()
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->pluck('size')
            ->toArray();
        return view('variants.index', compact('product', 'variants', 'availableSizes', 'search', 'sizeFilter', 'activeBranch', 'isAllBranches'));
    }

    public function create(Product $product)
    {
        return view('variants.create', compact('product'));
    }

    /**
     * Generate a concise SKU abbreviation for a variant.
     * Generates: [PREFIX]-[SIZE]-[COLOUR]
     * e.g. GWTS-24 or GWTS-24-NB
     */
    private function generateVariantSku(Product $product, ?string $size = null, ?string $colour = null): string
    {
        $prefix = strtoupper(trim($product->sku_prefix ?: 'PRD'));
        $tokens = [];

        if (!empty($size)) {
            $cleanSize = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size));
            if (!empty($cleanSize)) {
                $tokens[] = $cleanSize;
            }
        }

        if (!empty($colour) && strtolower($colour) !== 'standard') {
            $words = preg_split('/\s+/', trim($colour));
            if (count($words) > 1) {
                $abbr = implode('', array_map(fn($w) => strtoupper(substr($w, 0, 1)), $words));
            } else {
                $abbr = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $colour), 0, 4));
            }
            if (!empty($abbr)) {
                $tokens[] = $abbr;
            }
        }

        if (empty($tokens)) {
            $tokens[] = '1';
        }

        $baseSku = $prefix . '-' . implode('-', $tokens);
        $sku = $baseSku;
        $counter = 2;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$baseSku}-V{$counter}";
            $counter++;
        }

        return $sku;
    }

    /**
     * Resolves the selling price for a given size variant based on the pricing mode.
     */
    private function resolveSizePrice(string $size, string $mode, float $defaultPrice, array $groups, array $individualPrices): float
    {
        if ($mode === 'individual') {
            if (isset($individualPrices[$size]) && is_numeric($individualPrices[$size])) {
                return (float) $individualPrices[$size];
            }
            return $defaultPrice;
        }

        if ($mode === 'groups') {
            foreach ($groups as $group) {
                $grpPrice = isset($group['price']) && is_numeric($group['price']) ? (float) $group['price'] : null;
                if ($grpPrice === null) continue;

                // Check explicit comma-separated list
                if (!empty($group['sizes'])) {
                    $list = array_map('trim', explode(',', strtolower($group['sizes'])));
                    if (in_array(strtolower(trim($size)), $list)) {
                        return $grpPrice;
                    }
                }

                // Check numeric range (e.g. 22 to 26)
                $from = isset($group['from_size']) ? trim($group['from_size']) : '';
                $to   = isset($group['to_size']) ? trim($group['to_size']) : '';
                if (is_numeric($from) && is_numeric($to) && is_numeric($size)) {
                    $numSize = (float) $size;
                    $numFrom = min((float)$from, (float)$to);
                    $numTo   = max((float)$from, (float)$to);
                    if ($numSize >= $numFrom && $numSize <= $numTo) {
                        return $grpPrice;
                    }
                } elseif (!empty($from) && empty($to) && strtolower(trim($size)) === strtolower($from)) {
                    return $grpPrice;
                }
            }
        }

        return $defaultPrice;
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'size'          => ['nullable', 'string', 'max:20'],
            'colour'        => ['nullable', 'string', 'max:50'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $size   = trim($validated['size'] ?? '') ?: null;
        $colour = trim($validated['colour'] ?? '') ?: null;

        $sku = $this->generateVariantSku($product, $size, $colour);
        $barcode = 'BC-' . $sku;

        $product->variants()->create([
            'sku'             => $sku,
            'barcode'         => $barcode,
            'size'            => $size,
            'colour'          => $colour,
            'unit_of_measure' => 'Pieces',
            'selling_price'   => $validated['selling_price'],
            'stock_quantity'  => 0,
        ]);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    public function bulkCreate(Product $product)
    {
        // Standard primary, junior & secondary school uniform sizes
        $standardSizes = ['18', '20', '22', '24', '26', '28', '30', '32', '34', '36', '38', '40', '42', '44', 'S', 'M', 'L', 'XL', 'XXL'];

        return view('variants.bulk_create', compact('product', 'standardSizes'));
    }

    public function bulkStore(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sizes'          => ['nullable', 'array'],
            'custom_sizes'   => ['nullable', 'string'],
            'colour'         => ['nullable', 'string', 'max:100'],
            'pricing_mode'   => ['nullable', 'string', 'in:flat,groups,individual'],
            'selling_price'  => ['nullable', 'numeric', 'min:0'],
            'price_groups'   => ['nullable', 'array'],
            'price_groups.*.from_size' => ['nullable', 'string', 'max:20'],
            'price_groups.*.to_size'   => ['nullable', 'string', 'max:20'],
            'price_groups.*.sizes'     => ['nullable', 'string', 'max:100'],
            'price_groups.*.price'     => ['nullable', 'numeric', 'min:0'],
            'size_prices'    => ['nullable', 'array'],
            'size_prices.*'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $selectedSizes = $validated['sizes'] ?? [];
        $customSizesText = $validated['custom_sizes'] ?? '';

        $customSizes = array_map('trim', explode(',', str_replace(["\r\n", "\n", "\r"], ',', $customSizesText)));
        $allSizes = array_values(array_filter(array_unique(array_merge($selectedSizes, $customSizes))));

        if (empty($allSizes)) {
            return back()->withErrors(['sizes' => 'Please select or enter at least one size for the product.'])->withInput();
        }

        $colour = trim($validated['colour'] ?? '') ?: 'Standard';
        $pricingMode = $validated['pricing_mode'] ?? 'flat';
        $defaultPrice = isset($validated['selling_price']) && is_numeric($validated['selling_price'])
            ? (float) $validated['selling_price']
            : 0.00;
        $priceGroups = $validated['price_groups'] ?? [];
        $sizePrices = $validated['size_prices'] ?? [];

        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($product, $allSizes, $colour, $pricingMode, $defaultPrice, $priceGroups, $sizePrices, &$createdCount, &$skippedCount) {
            foreach ($allSizes as $cleanSize) {
                $sku     = $this->generateVariantSku($product, $cleanSize, $colour);
                $barcode = 'BC-' . $sku;

                if (ProductVariant::where('product_id', $product->id)->where('size', $cleanSize)->where('colour', $colour)->exists()) {
                    $skippedCount++;
                    continue;
                }

                $sellingPrice = $this->resolveSizePrice($cleanSize, $pricingMode, $defaultPrice, $priceGroups, $sizePrices);

                $product->variants()->create([
                    'sku'            => $sku,
                    'barcode'        => $barcode,
                    'size'           => $cleanSize,
                    'colour'         => $colour,
                    'selling_price'  => $sellingPrice,
                    'stock_quantity' => 0,
                ]);

                $createdCount++;
            }
        });

        $message = "Successfully created {$createdCount} new size variants with configured pricing.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} sizes already existed and were skipped).";
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
            'size'          => ['nullable', 'string', 'max:20'],
            'colour'        => ['nullable', 'string', 'max:50'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $size   = trim($validated['size'] ?? '') ?: null;
        $colour = trim($validated['colour'] ?? '') ?: null;

        // SKU and barcode are permanent once the item has been used: printed labels on garments carry
        // the barcode, and the shops and HQ match items by SKU. Only a brand-new, never-used variant
        // (e.g. a typo fixed straight after creating it) gets a fresh SKU and barcode.
        $sku = $variant->sku;
        $barcode = $variant->barcode;
        if (($size !== $variant->size || $colour !== $variant->colour) && !$variant->hasBeenUsed()) {
            $sku = $this->generateVariantSku($product, $size, $colour);
            $barcode = 'BC-' . $sku;
        }

        $variant->update([
            'sku'           => $sku,
            'barcode'       => $barcode,
            'size'          => $size,
            'colour'        => $colour,
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

    /**
     * Batch update variant prices across size ranges/groups (e.g. 22-26 @ 900, 28-30 @ 1000).
     */
    public function updateByPriceGroups(Request $request)
    {
        $validated = $request->validate([
            'target_type'              => ['nullable', 'string'],
            'category_id'              => ['nullable', 'exists:categories,id'],
            'product_id'               => ['nullable', 'exists:products,id'],
            'groups'                   => ['required', 'array', 'min:1'],
            'groups.*.from_size'       => ['nullable', 'string', 'max:20'],
            'groups.*.to_size'         => ['nullable', 'string', 'max:20'],
            'groups.*.sizes'           => ['nullable', 'string', 'max:100'],
            'groups.*.price'           => ['required', 'numeric', 'min:0'],
        ]);

        $targetType = $request->input('target_type', 'all');
        $totalUpdated = 0;
        $appliedRanges = [];

        DB::transaction(function () use ($validated, $targetType, &$totalUpdated, &$appliedRanges) {
            foreach ($validated['groups'] as $group) {
                $price = (float) $group['price'];
                $from  = trim($group['from_size'] ?? '');
                $to    = trim($group['to_size'] ?? '');
                $sizesList = !empty($group['sizes'])
                    ? array_map('trim', explode(',', $group['sizes']))
                    : [];

                // Base query matching scope
                $query = ProductVariant::query();

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
                } elseif (!empty($validated['product_id'])) {
                    $query->where('product_id', $validated['product_id']);
                } elseif (!empty($validated['category_id'])) {
                    $query->whereHas('product', fn($q) => $q->where('category_id', $validated['category_id']));
                }

                // Match sizes in range
                if (!empty($sizesList)) {
                    $query->whereIn('size', $sizesList);
                    $rangeDesc = implode(', ', $sizesList);
                } elseif (is_numeric($from) && is_numeric($to)) {
                    $numFrom = min((float)$from, (float)$to);
                    $numTo   = max((float)$from, (float)$to);
                    $query->whereRaw('size REGEXP "^[0-9]+(\\.[0-9]+)?$"')
                          ->whereRaw('CAST(size AS DECIMAL(10,2)) >= ? AND CAST(size AS DECIMAL(10,2)) <= ?', [$numFrom, $numTo]);
                    $rangeDesc = "Sizes {$numFrom}-{$numTo}";
                } elseif (!empty($from)) {
                    $query->where('size', $from);
                    $rangeDesc = "Size {$from}";
                } else {
                    continue;
                }

                $count = $query->update(['selling_price' => $price]);
                $totalUpdated += $count;
                $appliedRanges[] = "{$rangeDesc} @ KES " . number_format($price, 2);
            }
        });

        $summary = implode('; ', $appliedRanges);
        return back()->with('success', "Updated prices for {$totalUpdated} variant(s) across size groups: {$summary}.");
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

    public function searchAjax(Request $request)
    {
        $query = trim($request->input('q', ''));
        if ($query === '') {
            return response()->json([]);
        }

        $user = auth()->user();
        $branchName = $request->input('branch', $user?->branch_name ?: null);

        $variants = ProductVariant::with(['product.category', 'branchStocks'])
            ->where(function ($q) use ($query) {
                $q->whereHas('product', function ($p) use ($query) {
                    $p->where('name', 'like', "%{$query}%")
                      ->orWhere('sku_prefix', 'like', "%{$query}%");
                })
                ->orWhere('size', 'like', "%{$query}%")
                ->orWhere('colour', 'like', "%{$query}%")
                ->orWhere('brand_name', 'like', "%{$query}%")
                ->orWhere('unit_of_measure', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%")
                ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(30)
            ->get();

        return response()->json($variants->map(function ($variant) use ($branchName) {
            return [
                'id'              => $variant->id,
                'product_id'      => $variant->product_id,
                'product_name'    => $variant->product?->name ?? 'Product',
                'brand_name'      => $variant->brand_name,
                'unit_of_measure' => $variant->unit_of_measure ?? 'Piece',
                'formatted_title' => $variant->formatted_title,
                'category_name'   => $variant->product?->category?->name ?? 'General',
                'sku'             => $variant->sku,
                'barcode'         => $variant->barcode,
                'size'            => $variant->size,
                'colour'          => $variant->colour,
                'selling_price'   => number_format($variant->selling_price, 2),
                'stock_quantity'  => $variant->getStockForBranch($branchName),
                'edit_url'        => route('products.variants.edit', [$variant->product_id, $variant->id]),
                'variants_url'    => route('products.variants.index', $variant->product_id),
            ];
        }));
    }
}