<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">POS Terminal</h2>
            <a href="{{ route('sales.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                View Sales Log &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Search & Product Selection (2 Cols) --}}
                <div class="lg:col-span-2 bg-white shadow rounded-lg p-6 flex flex-col">
                    <h3 class="font-semibold text-gray-700 mb-2">Search Products</h3>
                    <div class="relative mb-4">
                        <input type="text" id="searchInput"
                            placeholder="Search by product name, size, colour, or SKU..."
                            class="block w-full border-gray-300 rounded-md shadow-sm text-sm pl-3 pr-10 py-2.5 focus:ring-indigo-500 focus:border-indigo-500"
                            oninput="searchProducts(this.value)"
                            autofocus>
                    </div>
                    <div id="searchResults" class="space-y-2 flex-1 overflow-y-auto max-h-[550px] pr-1">
                        <p class="text-gray-400 text-sm text-center py-12">Type at least 2 characters to search uniforms...</p>
                    </div>
                </div>

                {{-- Right: Cart & Checkout Panel (1 Col) --}}
                <div class="lg:col-span-1 bg-white shadow rounded-lg p-6 flex flex-col justify-between" style="min-height: 700px;">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-3 mb-4 flex justify-between items-center">
                            <span>Shopping Cart</span>
                            <button onclick="clearCart()" class="text-xs text-red-500 hover:underline font-normal">Clear</button>
                        </h3>

                        {{-- Customer Selection --}}
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Customer (Optional)</label>
                            <select id="customerSelect" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Walk-in Customer (General)</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                        {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Payment Method Selection --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Payment Method *</label>
                            <select id="paymentMethod" class="block w-full border-gray-300 rounded-md shadow-sm text-sm font-semibold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Cash">Cash</option>
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>

                        {{-- Cart Items Scrollable Container --}}
                        <div class="mb-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Cart Items</h4>
                            <div id="cartItems" class="max-h-[260px] overflow-y-auto pr-1 divide-y divide-gray-100">
                                <p class="text-gray-400 text-sm text-center py-8">No items added yet.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Anchored Footer: Total & Complete Sale Action --}}
                    <div class="border-t pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-600">Grand Total:</span>
                            <span id="cartTotal" class="font-extrabold text-2xl text-indigo-700">KES 0.00</span>
                        </div>

                        <button id="completeSaleBtn" onclick="checkout()"
                            style="background-color: #16a34a; color: white;"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-extrabold py-3.5 rounded-lg text-lg shadow-md transition-all duration-150 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                            <span>Complete Sale</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form --}}
    <form id="checkoutForm" method="POST" action="{{ route('pos.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="customer_id" id="formCustomerId">
        <input type="hidden" name="payment_method" id="formPaymentMethod">
        <div id="formItems"></div>
    </form>

    <script>
        let cart = [];

        async function searchProducts(query) {
            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML = '<p class="text-gray-400 text-sm text-center py-12">Type at least 2 characters to search uniforms...</p>';
                return;
            }
            const response = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}`);
            const variants = await response.json();
            if (variants.length === 0) {
                document.getElementById('searchResults').innerHTML = '<p class="text-gray-400 text-sm text-center py-12">No matching uniform variants found.</p>';
                return;
            }
            document.getElementById('searchResults').innerHTML = variants.map(v => `
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-indigo-50/50 hover:border-indigo-300 cursor-pointer transition mb-2 bg-white"
                     onclick="addToCart(${v.id}, '${escapeJs(v.product_name)}', '${escapeJs(v.sku)}', '${escapeJs(v.size)}', '${escapeJs(v.colour)}', ${v.selling_price}, ${v.stock_quantity})">
                    <div>
                        <p class="font-bold text-sm text-gray-900">${v.product_name}</p>
                        <p class="text-xs text-gray-600 font-mono">${v.sku} — Size: <span class="font-bold">${v.size}</span> | <span class="font-bold">${v.colour}</span></p>
                        <p class="text-xs ${v.stock_quantity <= 5 ? 'text-red-600 font-bold' : 'text-gray-500'}">Available Stock: ${v.stock_quantity} units</p>
                    </div>
                    <div class="text-right">
                        <p class="font-extrabold text-indigo-700 text-sm">KES ${parseFloat(v.selling_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                        <span class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1 rounded shadow-sm mt-1 inline-block">+ Add</span>
                    </div>
                </div>
            `).join('');
        }

        function escapeJs(str) {
            return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        function addToCart(id, productName, sku, size, colour, price, stock) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.quantity >= stock) { alert('Cannot exceed available stock quantity (' + stock + ').'); return; }
                existing.quantity++;
            } else {
                cart.push({ id, productName, sku, size, colour, price, quantity: 1, stock });
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function updateQuantity(id, qty) {
            const item = cart.find(i => i.id === id);
            if (!item) return;
            const newQty = parseInt(qty);
            if (isNaN(newQty) || newQty < 1) { removeFromCart(id); return; }
            if (newQty > item.stock) { alert('Cannot exceed available stock (' + item.stock + ').'); return; }
            item.quantity = newQty;
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">No items added yet.</p>';
                document.getElementById('cartTotal').textContent = 'KES 0.00';
                return;
            }
            let total = 0;
            container.innerHTML = cart.map(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                return `<div class="flex justify-between items-center py-2 text-xs">
                    <div class="flex-1 pr-2">
                        <p class="font-bold text-gray-900">${item.productName}</p>
                        <p class="text-gray-500">Size ${item.size} | ${item.colour}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-gray-500">Qty:</span>
                            <input type="number" value="${item.quantity}" min="1" max="${item.stock}"
                                onchange="updateQuantity(${item.id}, this.value)"
                                class="w-12 text-center border-gray-300 rounded text-xs py-0.5 font-bold">
                            <span class="text-indigo-700 font-bold">@ KES ${item.price.toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="text-right flex items-center gap-2">
                        <span class="text-green-700 font-extrabold text-sm">KES ${subtotal.toFixed(2)}</span>
                        <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 font-bold text-base px-1">&times;</button>
                    </div>
                </div>`;
            }).join('');
            document.getElementById('cartTotal').textContent = `KES ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        }

        function checkout() {
            if (cart.length === 0) { alert('Please add items to the cart first.'); return; }
            document.getElementById('formCustomerId').value = document.getElementById('customerSelect').value;
            document.getElementById('formPaymentMethod').value = document.getElementById('paymentMethod').value;
            const itemsContainer = document.getElementById('formItems');
            itemsContainer.innerHTML = cart.map((item, index) => `
                <input type="hidden" name="items[${index}][variant_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            `).join('');
            document.getElementById('checkoutForm').submit();
        }
    </script>
</x-app-layout>