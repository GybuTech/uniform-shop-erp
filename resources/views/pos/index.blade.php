<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                POS / Sales Terminal
            </h2>
            <a href="{{ route('sales.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                View Sales Log &rarr;
            </a>
        </div>
    </x-slot>

    <!-- Alpine POS Application -->
    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Product Search & Inventory Grid (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">

            <!-- Search input bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <label for="barcode_search" class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                    Scan Barcode / Search Product Name or SKU
                </label>
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.250ms="filterProducts()"
                           placeholder="Type product name, SKU, or scan barcode..."
                           class="w-full pl-10 pr-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Product Variants List Grid -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 h-[520px] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="item in filteredVariants" :key="item.id">
                        <div @click="addToCart(item)"
                             class="p-3 border rounded-xl hover:border-indigo-500 hover:shadow-md cursor-pointer transition flex flex-col justify-between bg-gradient-to-br from-white to-gray-50">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="font-bold text-gray-900 text-sm" x-text="item.product ? item.product.name : 'Product'"></span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700" x-text="item.size + ' / ' + item.colour"></span>
                                </div>
                                <div class="text-xs text-gray-500 font-mono mt-1" x-text="'SKU: ' + item.sku + ' | Barcode: ' + item.barcode"></div>
                            </div>
                            <div class="flex justify-between items-end mt-3 pt-2 border-t border-gray-100">
                                <div>
                                    <span class="text-xs text-gray-500 block">Stock</span>
                                    <span class="text-xs font-bold" :class="item.stock_quantity <= 5 ? 'text-red-600' : 'text-emerald-600'" x-text="item.stock_quantity + ' units'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-indigo-700" x-text="'KES ' + Number(item.selling_price).toLocaleString()"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filteredVariants.length === 0" class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    No matching available product variants found.
                </div>
            </div>
        </div>

        <!-- Right Column: Shopping Cart & Checkout (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-[600px]">
                <h3 class="font-bold text-gray-900 text-lg border-b pb-3 flex justify-between items-center">
                    <span>Current Cart</span>
                    <button x-show="cart.length > 0" @click="clearCart()" class="text-xs text-red-600 hover:underline">Clear All</button>
                </h3>

                <!-- Cart Items Table / List -->
                <div class="flex-1 overflow-y-auto my-3 divide-y divide-gray-100">
                    <template x-for="(cartItem, index) in cart" :key="cartItem.id">
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex-1 pr-2">
                                <div class="font-semibold text-gray-900 text-sm" x-text="cartItem.product ? cartItem.product.name : 'Item'"></div>
                                <div class="text-xs text-gray-500" x-text="cartItem.size + ' / ' + cartItem.colour + ' (KES ' + Number(cartItem.selling_price).toLocaleString() + ')'"></div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 font-bold text-sm text-gray-700 flex items-center justify-center">-</button>
                                <span class="font-bold text-sm w-6 text-center" x-text="cartItem.qty"></span>
                                <button @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 font-bold text-sm text-gray-700 flex items-center justify-center">+</button>
                            </div>
                            <div class="w-24 text-right">
                                <span class="font-bold text-sm text-gray-900" x-text="'KES ' + (cartItem.selling_price * cartItem.qty).toLocaleString()"></span>
                            </div>
                            <button @click="removeFromCart(index)" class="ml-2 text-gray-400 hover:text-red-600">
                                &times;
                            </button>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="text-center py-16 text-gray-400">
                        Cart is empty. Select items to add.
                    </div>
                </div>

                <!-- Checkout Form Controls -->
                <div class="border-t pt-4 space-y-3 bg-gray-50 -mx-5 -mb-5 p-5 rounded-b-xl">
                    <!-- Customer selection -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Customer (Optional)</label>
                        <select x-model="selectedCustomerId" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Walk-in Customer (General)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment method selection -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Payment Method *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="paymentMethod = 'Cash'" :class="paymentMethod === 'Cash' ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-gray-700 border hover:bg-gray-100'" class="py-2 text-sm rounded-lg transition">Cash</button>
                            <button type="button" @click="paymentMethod = 'M-Pesa'" :class="paymentMethod === 'M-Pesa' ? 'bg-emerald-600 text-white font-bold' : 'bg-white text-gray-700 border hover:bg-gray-100'" class="py-2 text-sm rounded-lg transition">M-Pesa</button>
                            <button type="button" @click="paymentMethod = 'Card'" :class="paymentMethod === 'Card' ? 'bg-blue-600 text-white font-bold' : 'bg-white text-gray-700 border hover:bg-gray-100'" class="py-2 text-sm rounded-lg transition">Card</button>
                        </div>
                    </div>

                    <!-- Total Amount Display -->
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm font-semibold text-gray-600">Grand Total:</span>
                        <span class="text-2xl font-extrabold text-indigo-700" x-text="'KES ' + totalCartAmount.toLocaleString()"></span>
                    </div>

                    <!-- Submit Sale Button -->
                    <button @click="processSale()"
                            :disabled="cart.length === 0 || isProcessing"
                            class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg rounded-xl shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">Complete Sale & Print Receipt</span>
                        <span x-show="isProcessing">Processing Sale...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass server product data to Alpine -->
    <script>
        function posApp() {
            return {
                allVariants: @json($variants),
                filteredVariants: [],
                searchQuery: '',
                cart: [],
                selectedCustomerId: '',
                paymentMethod: 'Cash',
                isProcessing: false,

                init() {
                    this.filteredVariants = this.allVariants;
                },

                filterProducts() {
                    const q = this.searchQuery.toLowerCase().trim();
                    if (!q) {
                        this.filteredVariants = this.allVariants;
                        return;
                    }

                    this.filteredVariants = this.allVariants.filter(v => {
                        const productName = v.product ? v.product.name.toLowerCase() : '';
                        const sku = v.sku.toLowerCase();
                        const barcode = v.barcode.toLowerCase();
                        return productName.includes(q) || sku.includes(q) || barcode.includes(q);
                    });
                },

                addToCart(variant) {
                    const existingIndex = this.cart.findIndex(i => i.id === variant.id);
                    if (existingIndex > -1) {
                        if (this.cart[existingIndex].qty < variant.stock_quantity) {
                            this.cart[existingIndex].qty++;
                        } else {
                            alert('Cannot add more units than available stock (' + variant.stock_quantity + ')');
                        }
                    } else {
                        this.cart.push({
                            id: variant.id,
                            product: variant.product,
                            size: variant.size,
                            colour: variant.colour,
                            selling_price: variant.selling_price,
                            stock_quantity: variant.stock_quantity,
                            qty: 1
                        });
                    }
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    const newQty = item.qty + delta;
                    if (newQty <= 0) {
                        this.removeFromCart(index);
                    } else if (newQty <= item.stock_quantity) {
                        item.qty = newQty;
                    } else {
                        alert('Max available stock is ' + item.stock_quantity);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    this.cart = [];
                },

                get totalCartAmount() {
                    return this.cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
                },

                async processSale() {
                    if (this.cart.length === 0) return;

                    this.isProcessing = true;
                    try {
                        const response = await fetch("{{ route('pos.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                customer_id: this.selectedCustomerId || null,
                                payment_method: this.paymentMethod,
                                items: this.cart.map(i => ({ id: i.id, qty: i.qty }))
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.href = data.receipt_url;
                        } else {
                            alert(data.message || 'Sale failed.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Error processing sale.');
                    } finally {
                        this.isProcessing = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
