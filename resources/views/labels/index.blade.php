<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">Print Barcode Labels</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Stickers or swing tags for the garments you make. The till's scanner reads them straight into the sale.
                </p>
            </div>
            <a href="{{ route('inventory.factory-receipts') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs border transition">
                Record Workshop Delivery
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-5"
             x-data="labelPicker(@js($items))">

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-300 text-rose-800 p-3.5 rounded-lg text-xs">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-950 space-y-1">
                <strong class="text-sm block">How it works</strong>
                <span>1. Record the batch from the workshop under <em>Stock Intake &rarr; Workshop / Factory Receipts</em> (it then offers to print one label per piece), or add items below.</span><br>
                <span>2. Check the number of labels for each size, pick your label size and press <strong>Print Labels</strong>.</span><br>
                <span>3. Stick or tag the labels on the garments. At the till, scanning a label adds that exact size and colour to the sale.</span>
                @if($source)
                    <div class="pt-1 font-bold">Loaded from: {{ $source }}</div>
                @endif
            </div>

            {{-- Find items to add --}}
            <div class="bg-white p-4 rounded-xl border shadow-sm space-y-3">
                <label class="block text-xs font-bold text-gray-700">Add items</label>
                <div class="relative">
                    <input type="text" x-model="query" @input.debounce.300ms="search()" @keydown.enter.prevent="search()"
                           placeholder="Type a product name, size, SKU or barcode (e.g. Sweater 24)..."
                           class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <span x-show="loading" class="absolute right-3 top-2.5 text-xs text-gray-400">Searching...</span>
                </div>
                <div x-show="results.length" class="border rounded-lg divide-y max-h-72 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div class="flex items-center justify-between px-3 py-2 text-xs hover:bg-gray-50">
                            <div>
                                <span class="font-bold text-gray-800" x-text="r.product_name"></span>
                                <span class="text-gray-500" x-text="[r.size ? 'Size ' + r.size : '', r.colour].filter(Boolean).join(' · ')"></span>
                                <span class="ml-2 font-mono text-gray-400" x-text="r.barcode || r.sku"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-gray-500">In stock: <span x-text="Number(r.stock_quantity)"></span></span>
                                <button type="button" @click="add(r)" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded">Add</button>
                            </div>
                        </div>
                    </template>
                </div>
                <p x-show="searched && !loading && !results.length" class="text-xs text-gray-400">No items found.</p>
            </div>

            <form method="POST" action="{{ route('labels.print') }}" target="_blank" class="space-y-5">
                @csrf

                {{-- Chosen items --}}
                <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b bg-gray-50">
                        <span class="text-xs font-bold text-gray-700">
                            Labels to print: <span x-text="total()"></span>
                        </span>
                        <div class="flex flex-wrap gap-2 text-xs" x-show="items.length">
                            <button type="button" @click="setAll(1)" class="px-2.5 py-1 border rounded hover:bg-white">1 each</button>
                            <button type="button" @click="setToStock()" class="px-2.5 py-1 border rounded hover:bg-white">Match stock at {{ $branch }}</button>
                            <button type="button" @click="items = []" class="px-2.5 py-1 border rounded text-rose-600 hover:bg-white">Clear</button>
                        </div>
                    </div>
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Item</th>
                                <th class="px-4 py-2 text-left">Barcode</th>
                                <th class="px-4 py-2 text-right">Price</th>
                                <th class="px-4 py-2 text-right">In stock</th>
                                <th class="px-4 py-2 text-center w-32">Labels</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="(item, index) in items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-2">
                                        <span class="font-bold text-gray-800" x-text="item.name"></span>
                                        <span class="text-gray-500" x-text="[item.size ? 'Size ' + item.size : '', item.colour].filter(Boolean).join(' · ')"></span>
                                        <input type="hidden" :name="`items[${index}][variant_id]`" :value="item.id">
                                    </td>
                                    <td class="px-4 py-2 font-mono text-gray-500" x-text="item.barcode || item.sku"></td>
                                    <td class="px-4 py-2 text-right" x-text="item.price"></td>
                                    <td class="px-4 py-2 text-right" x-text="Number(item.stock)"></td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="number" min="0" max="2000" step="1" x-model.number="item.quantity"
                                               :name="`items[${index}][quantity]`"
                                               class="w-20 border-gray-300 rounded text-xs text-center">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" @click="items.splice(index, 1)" class="text-rose-600 hover:underline">Remove</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!items.length">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">No items yet. Search above to add them.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Label options --}}
                <div class="bg-white p-4 rounded-xl border shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Label size</label>
                        <select name="label_size" x-model="labelSize" class="w-full border-gray-300 rounded text-xs">
                            @foreach($sizes as $key => $s)
                                <option value="{{ $key }}">{{ $s['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="labelSize === 'a4'">
                        <label class="block font-bold text-gray-700 mb-1">Skip used stickers on the sheet</label>
                        <input type="number" name="skip" min="0" max="23" value="0" class="w-full border-gray-300 rounded text-xs">
                    </div>
                    <label class="flex items-center gap-2 font-bold text-gray-700 mt-5">
                        <input type="hidden" name="show_price" value="0">
                        <input type="checkbox" name="show_price" value="1" @checked($showPrice) class="rounded border-gray-300">
                        Show price on label
                    </label>
                    <label class="flex items-center gap-2 font-bold text-gray-700 mt-5">
                        <input type="hidden" name="show_business" value="0">
                        <input type="checkbox" name="show_business" value="1" @checked($showBusiness) class="rounded border-gray-300">
                        Show business name
                    </label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" :disabled="!total()"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white text-sm font-bold rounded-lg shadow-sm">
                        Print Labels
                    </button>
                    <button type="submit" name="preview" value="1" :disabled="!total()"
                            class="px-5 py-2.5 bg-white border hover:bg-gray-50 disabled:opacity-40 text-gray-700 text-sm font-bold rounded-lg">
                        Preview first
                    </button>
                </div>
                <p class="text-[11px] text-gray-500">
                    Opens in a new tab. In the print box choose the <strong>label printer</strong> (not the receipt printer), margins <strong>None</strong>, scale <strong>100%</strong>.
                    Setup steps: <em>docs/Receipt Printer Setup.md</em>, section "Barcode labels".
                </p>
            </form>
        </div>
    </div>

    <script>
        function labelPicker(initialItems) {
            return {
                items: initialItems,
                results: [],
                query: '',
                loading: false,
                searched: false,
                labelSize: @js($labelSize),
                total() {
                    return this.items.reduce((sum, i) => sum + Math.max(0, parseInt(i.quantity) || 0), 0);
                },
                setAll(n) { this.items.forEach(i => i.quantity = n); },
                setToStock() { this.items.forEach(i => i.quantity = Math.max(0, Math.round(Number(i.stock) || 0))); },
                add(r) {
                    const existing = this.items.find(i => i.id === r.id);
                    if (existing) { existing.quantity = (parseInt(existing.quantity) || 0) + 1; return; }
                    this.items.push({
                        id: r.id, name: r.product_name, size: r.size, colour: r.colour,
                        sku: r.sku, barcode: r.barcode, price: r.selling_price,
                        stock: r.stock_quantity, quantity: 1,
                    });
                },
                search() {
                    const q = this.query.trim();
                    if (q.length < 2) { this.results = []; this.searched = false; return; }
                    this.loading = true;
                    fetch(`{{ route('products.variants.search') }}?q=${encodeURIComponent(q)}&branch=${encodeURIComponent(@js($branch))}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => { this.results = Array.isArray(data) ? data : []; })
                        .catch(() => { this.results = []; })
                        .finally(() => { this.loading = false; this.searched = true; });
                },
            };
        }
    </script>
</x-app-layout>
