<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockEntry;
use App\Services\BranchContext;
use App\Services\Code128;
use Illuminate\Http\Request;

/**
 * Barcode labels for the garments the shop makes (sweaters, tracksuits, fleece jackets...) or buys.
 *
 * Each size/colour (variant) already has a permanent barcode such as "BC-GWTS-24-NB". This screen
 * prints it as a sticker or swing-tag so the till can scan it. Works fully offline.
 */
class LabelController extends Controller
{
    /** Label stock the shop can print on. Width/height in millimetres; 'a4' is a sheet of stickers. */
    public const SIZES = [
        '50x25' => ['label' => '50 x 25 mm roll (recommended)', 'w' => 50, 'h' => 25],
        '40x30' => ['label' => '40 x 30 mm roll', 'w' => 40, 'h' => 30],
        '38x25' => ['label' => '38 x 25 mm roll (small; short barcodes only)', 'w' => 38, 'h' => 25],
        '60x40' => ['label' => '60 x 40 mm roll / swing tag', 'w' => 60, 'h' => 40],
        'a4'    => ['label' => 'A4 sticker sheet, 3 x 8 (70 x 37 mm)', 'w' => 70, 'h' => 37],
    ];

    private const MAX_LABELS = 2000;

    public function index(Request $request)
    {
        $branch = BranchContext::getActiveBranch() ?: (auth()->user()->branch_name ?: config('app.default_branch'));
        $items = collect();
        $source = null;

        // Pre-fill from a workshop delivery / stock intake: one label per piece received
        if ($request->filled('entry')) {
            $entry = StockEntry::with('items.productVariant.product')->find($request->integer('entry'));
            if ($entry) {
                $source = 'Delivery ' . ($entry->delivery_note_no ?: $entry->reference_no ?: '#' . $entry->id);
                foreach ($entry->items as $line) {
                    if ($line->productVariant) {
                        $items->push($this->row($line->productVariant, (int) round((float) $line->quantity), $branch));
                    }
                }
            }
        }

        // Pre-fill with every size of one product (from the product's variants page)
        if ($request->filled('product')) {
            $product = Product::with('variants')->find($request->integer('product'));
            if ($product) {
                $source = $source ?: $product->name;
                foreach ($product->variants->sortBy('id') as $variant) {
                    $items->push($this->row($variant, 1, $branch));
                }
            }
        }

        return view('labels.index', [
            'items'        => $items->values(),
            'source'       => $source,
            'sizes'        => self::SIZES,
            'labelSize'    => BusinessSetting::get('label_size', '50x25'),
            'showPrice'    => BusinessSetting::get('label_show_price', '1') === '1',
            'showBusiness' => BusinessSetting::get('label_show_business', '1') === '1',
            'branch'       => $branch,
        ]);
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'items'               => ['required', 'array', 'min:1'],
            'items.*.variant_id'  => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:0', 'max:' . self::MAX_LABELS],
            'label_size'          => ['required', 'in:' . implode(',', array_keys(self::SIZES))],
            'show_price'          => ['nullable', 'boolean'],
            'show_business'       => ['nullable', 'boolean'],
            'skip'                => ['nullable', 'integer', 'min:0', 'max:23'],
        ]);

        $wanted = collect($validated['items'])->filter(fn ($i) => (int) $i['quantity'] > 0);
        $total = $wanted->sum(fn ($i) => (int) $i['quantity']);

        if ($total === 0) {
            return back()->withErrors(['items' => 'Enter how many labels to print for at least one item.'])->withInput();
        }
        if ($total > self::MAX_LABELS) {
            return back()->withErrors(['items' => 'That is ' . number_format($total) . ' labels. Print at most ' . number_format(self::MAX_LABELS) . ' at a time.'])->withInput();
        }

        // Remember the shop's label choices for next time
        BusinessSetting::set('label_size', $validated['label_size']);
        BusinessSetting::set('label_show_price', $request->boolean('show_price') ? '1' : '0');
        BusinessSetting::set('label_show_business', $request->boolean('show_business') ? '1' : '0');

        $variants = ProductVariant::with('product')->whereIn('id', $wanted->pluck('variant_id'))->get()->keyBy('id');
        $labels = [];
        $svgCache = [];

        foreach ($wanted as $line) {
            $variant = $variants[(int) $line['variant_id']] ?? null;
            if (!$variant) {
                continue;
            }
            $code = $this->ensureBarcode($variant);
            $svgCache[$code] ??= Code128::svg($code);

            $label = [
                'name'    => $variant->product?->name ?? 'Item',
                'details' => collect([
                    $variant->size ? 'Size ' . $variant->size : null,
                    $variant->colour && strtolower($variant->colour) !== 'standard' ? $variant->colour : null,
                ])->filter()->join(' · '),
                'price'   => (float) $variant->selling_price,
                'code'    => $code,
                'svg'     => $svgCache[$code],
            ];

            for ($n = 0; $n < (int) $line['quantity']; $n++) {
                $labels[] = $label;
            }
        }

        $size = self::SIZES[$validated['label_size']];

        return view('labels.print', [
            'labels'       => $labels,
            'sizeKey'      => $validated['label_size'],
            'size'         => $size,
            'showPrice'    => $request->boolean('show_price'),
            'showBusiness' => $request->boolean('show_business'),
            'businessName' => BusinessSetting::get('business_name', 'Tshaprint Solutions'),
            'skip'         => $validated['label_size'] === 'a4' ? (int) ($validated['skip'] ?? 0) : 0,
            'preview'      => $request->boolean('preview'),
        ]);
    }

    private function row(ProductVariant $variant, int $quantity, ?string $branch): array
    {
        return [
            'id'       => $variant->id,
            'name'     => $variant->product?->name ?? 'Item',
            'size'     => $variant->size,
            'colour'   => $variant->colour,
            'sku'      => $variant->sku,
            'barcode'  => $variant->barcode,
            'price'    => number_format((float) $variant->selling_price, 2),
            'stock'    => $variant->getStockForBranch($branch),
            'quantity' => max(0, $quantity),
        ];
    }

    /** Older or imported items might have an empty or unprintable barcode: give them one once. */
    private function ensureBarcode(ProductVariant $variant): string
    {
        $code = trim((string) $variant->barcode);
        if ($code !== '' && preg_match('/^[\x20-\x7E]+$/', $code)) {
            return $code;
        }

        $base = 'BC-' . preg_replace('/[^A-Za-z0-9\-]/', '', (string) $variant->sku);
        $code = $base !== 'BC-' ? $base : 'BC-V' . $variant->id;
        $candidate = $code;
        $n = 2;
        while (ProductVariant::withTrashed()->where('barcode', $candidate)->where('id', '!=', $variant->id)->exists()) {
            $candidate = $code . '-' . $n++;
        }
        $variant->forceFill(['barcode' => $candidate])->save();

        return $candidate;
    }
}
