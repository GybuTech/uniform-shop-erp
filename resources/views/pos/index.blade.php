<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">POS Terminal</h2>
            <a href="{{ route('sales.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                View Sales Log &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->has('pos_error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm text-sm flex justify-between items-center">
                    <div>
                        <strong class="font-bold">Checkout Failed:</strong>
                        <span>{{ $errors->first('pos_error') }}</span>
                    </div>
                </div>
            @endif

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

                        {{-- Branch Indicator / Selector --}}
                        @if($isLockedBranch)
                            <div class="mb-3 p-3 bg-blue-50/80 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Terminal Branch</span>
                                    <span class="text-[10px] bg-blue-200 text-blue-900 font-bold px-2 py-0.5 rounded-full">Assigned</span>
                                </div>
                                <p class="text-sm font-extrabold text-blue-950 mt-1 flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-blue-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>{{ $currentBranchName }}</span>
                                </p>
                                <input type="hidden" id="branchSelect" value="{{ $currentBranchName }}">
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Selling Branch *</label>
                                    <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded">Owner Override</span>
                                </div>
                                <select id="branchSelect" class="block w-full border-gray-300 rounded-md shadow-sm text-xs font-bold text-indigo-900 bg-indigo-50/50 focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->name }}" {{ ($currentBranchName == $branch->name) ? 'selected' : '' }}>
                                            {{ $branch->name }} {{ $branch->code ? '('.$branch->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

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

                        {{-- Cart Items List (Scrollable) --}}
                        <div class="border-t pt-3">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Cart Items</span>
                            <div id="cartList" class="space-y-2 overflow-y-auto max-h-[260px] pr-1">
                                <p class="text-gray-400 text-xs text-center py-6 border border-dashed rounded">Cart is empty</p>
                            </div>
                        </div>
                    </div>

                    {{-- Anchored Footer: Grand Total + Checkout Action --}}
                    <div class="border-t pt-4 mt-4 bg-gray-50 p-4 -mx-6 -mb-6 rounded-b-lg">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-600 font-bold text-sm">Grand Total</span>
                            <span id="grandTotal" class="text-2xl font-extrabold text-green-600">KES 0.00</span>
                        </div>

                        <form id="checkoutForm" method="POST" action="{{ route('pos.store') }}" onsubmit="return validateCheckout()">
                            @csrf
                            <div id="hiddenInputs"></div>
                            <input type="hidden" name="customer_id" id="formCustomerId">
                            <input type="hidden" name="payment_method" id="formPaymentMethod">
                            <input type="hidden" name="branch_name" id="formBranchName">

                            <button type="submit" id="checkoutBtn" disabled
                                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-extrabold py-3.5 px-4 rounded-lg shadow transition text-base tracking-wide flex items-center justify-center space-x-2">
                                <span>Complete Sale</span>
                                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let searchTimeout = null;

        function searchProducts(query) {
            clearTimeout(searchTimeout);
            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML =
                    '<p class="text-gray-400 text-sm text-center py-12">Type at least 2 characters to search uniforms...</p>';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => renderSearchResults(data));
            }, 300);
        }

        function renderSearchResults(variants) {
            const container = document.getElementById('searchResults');

            if (variants.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm text-center py-12">No matching uniform items found.</p>';
                return;
            }

            container.innerHTML = variants.map(v => {
                const normColour = (v.colour || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const normName = (v.product_name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const showColour = v.colour && normColour !== normName && !normName.includes(normColour);
                const colourTag = showColour ? ` &bull; <span class="text-indigo-600 font-medium">${v.colour}</span>` : '';
                const safeName = (v.product_name || '').replace(/'/g, "\\'");
                const safeColour = (v.colour || '').replace(/'/g, "\\'");

                const stockBadge = v.stock_quantity <= 0
                    ? `<span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Out of Stock (0 pcs)</span>`
                    : (v.stock_quantity <= 5
                        ? `<span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Low Stock: ${v.stock_quantity} pcs</span>`
                        : `<span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700">In Stock: ${v.stock_quantity} pcs</span>`);

                const actionButton = v.stock_quantity <= 0
                    ? `<span class="inline-block mt-1 text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded font-semibold border">0 Stock</span>`
                    : `<span class="inline-block mt-1 text-xs bg-indigo-600 text-white px-2.5 py-1 rounded font-bold hover:bg-indigo-700">+ Add</span>`;

                return `
                <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-indigo-50 transition cursor-pointer"
                     onclick="addToCart(${v.id}, '${safeName}', '${v.size}', '${safeColour}', ${v.selling_price}, ${v.stock_quantity})">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">${v.product_name}</p>
                        <p class="text-xs text-gray-500 font-mono mb-1"><span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-semibold">${v.sku}</span> &bull; Size: <strong class="text-gray-800">${v.size}</strong>${colourTag}</p>
                        ${stockBadge}
                    </div>
                    <div class="text-right">
                        <p class="font-extrabold text-indigo-600 text-base">KES ${parseFloat(v.selling_price).toFixed(2)}</p>
                        ${actionButton}
                    </div>
                </div>
            `;
            }).join('');
        }

        function addToCart(id, name, size, colour, price, stock) {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.qty + 1 > stock) {
                    alert(`Cannot add more units. Available stock limit for ${name} (${size}) is ${stock}.`);
                    return;
                }
                existing.qty++;
            } else {
                if (stock < 1) {
                    alert(`Notice: ${name} (Size ${size}) is currently out of stock (0 units on hand). Please intake stock before completing sales.`);
                    return;
                }
                cart.push({ id, name, size, colour, price, stock, qty: 1 });
            }
            renderCart();
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.qty + delta;

            if (newQty > item.stock) {
                alert(`Cannot exceed available stock of ${item.stock} units.`);
                return;
            }

            if (newQty <= 0) {
                removeFromCart(id);
            } else {
                item.qty = newQty;
                renderCart();
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const cartList = document.getElementById('cartList');
            const grandTotalEl = document.getElementById('grandTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');

            if (cart.length === 0) {
                cartList.innerHTML = '<p class="text-gray-400 text-xs text-center py-6 border border-dashed rounded">Cart is empty</p>';
                grandTotalEl.innerText = 'KES 0.00';
                checkoutBtn.disabled = true;
                return;
            }

            let total = 0;
            cartList.innerHTML = cart.map(item => {
                const lineTotal = item.price * item.qty;
                total += lineTotal;
                const normColour = (item.colour || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const normName = (item.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const showColour = item.colour && normColour !== normName && !normName.includes(normColour);
                const itemDetail = `Size: ${item.size}` + (showColour ? ` | ${item.colour}` : '');

                return `
                    <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded border text-xs">
                        <div class="flex-1 pr-2">
                            <p class="font-bold text-gray-900">${item.name}</p>
                            <p class="text-gray-500">${itemDetail}</p>
                            <p class="font-semibold text-indigo-600">KES ${parseFloat(item.price).toFixed(2)}</p>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <button onclick="updateQty(${item.id}, -1)" class="px-2 py-0.5 bg-gray-200 hover:bg-gray-300 rounded font-bold text-gray-700">-</button>
                            <span class="font-extrabold w-6 text-center text-sm">${item.qty}</span>
                            <button onclick="updateQty(${item.id}, 1)" class="px-2 py-0.5 bg-gray-200 hover:bg-gray-300 rounded font-bold text-gray-700">+</button>
                            <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 ml-1.5 font-bold">✕</button>
                        </div>
                    </div>
                `;
            }).join('');

            grandTotalEl.innerText = `KES ${total.toFixed(2)}`;
            checkoutBtn.disabled = false;
        }

        function validateCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty.');
                return false;
            }

            for (let item of cart) {
                if (item.qty <= 0 || item.qty > item.stock) {
                    alert(`Invalid quantity for ${item.name}. Available stock: ${item.stock}`);
                    return false;
                }
            }

            const hiddenContainer = document.getElementById('hiddenInputs');
            hiddenContainer.innerHTML = cart.map((item, i) => `
                <input type="hidden" name="items[${i}][variant_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][quantity]" value="${item.qty}">
            `).join('');

            document.getElementById('formCustomerId').value = document.getElementById('customerSelect').value;
            document.getElementById('formPaymentMethod').value = document.getElementById('paymentMethod').value;
            document.getElementById('formBranchName').value = document.getElementById('branchSelect').value;

            return true;
        }
    </script>
</x-app-layout>