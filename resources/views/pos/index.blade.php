<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">POS Terminal</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Product Search --}}
                <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Search Products</h3>

                    <div class="flex gap-2 mb-4">
                        <input type="text" id="searchInput"
                            placeholder="Search by product name, size, or colour..."
                            class="block w-full border-gray-300 rounded-md shadow-sm text-sm"
                            oninput="searchProducts(this.value)">
                    </div>

                    <div id="searchResults" class="space-y-2 max-h-96 overflow-y-auto">
                        <p class="text-gray-400 text-sm">Type to search products...</p>
                    </div>
                </div>

                {{-- Right: Cart --}}
                <div class="bg-white shadow rounded-lg p-6 flex flex-col">
                    <h3 class="font-semibold text-gray-700 mb-4">Current Sale</h3>

                    {{-- Customer Selection --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Customer (Optional)</label>
                        <select id="customerSelect" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                    {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cart Items --}}
                    <div id="cartItems" class="flex-1 space-y-2 mb-4 min-h-32">
                        <p id="emptyCart" class="text-gray-400 text-sm">No items added yet.</p>
                    </div>

                    {{-- Total --}}
                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span id="cartTotal">KES 0.00</span>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Payment Method</label>
                        <select id="paymentMethod" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="Cash">Cash</option>
                            <option value="M-Pesa">M-Pesa</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>

                    {{-- Checkout Button --}}
                    <button onclick="checkout()"
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700">
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for submission --}}
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
                document.getElementById('searchResults').innerHTML = '<p class="text-gray-400 text-sm">Type to search products...</p>';
                return;
            }

            const response = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}`);
            const variants = await response.json();

            if (variants.length === 0) {
                document.getElementById('searchResults').innerHTML = '<p class="text-gray-400 text-sm">No products found.</p>';
                return;
            }

            document.getElementById('searchResults').innerHTML = variants.map(v => `
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
                     onclick="addToCart(${v.id}, '${v.product_name}', '${v.sku}', '${v.size}', '${v.colour}', ${v.selling_price}, ${v.stock_quantity})">
                    <div>
                        <p class="font-medium text-sm text-gray-900">${v.product_name}</p>
                        <p class="text-xs text-gray-500">${v.sku} — Size: ${v.size} | Colour: ${v.colour}</p>
                        <p class="text-xs text-gray-400">Stock: ${v.stock_quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">KES ${parseFloat(v.selling_price).toFixed(2)}</p>
                        <button class="text-xs bg-indigo-600 text-white px-2 py-1 rounded mt-1">Add</button>
                    </div>
                </div>
            `).join('');
        }

        function addToCart(id, productName, sku, size, colour, price, stock) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.quantity >= stock) {
                    alert('Cannot add more than available stock.');
                    return;
                }
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

        function updateQuantity(id, qty) {
            const item = cart.find(i => i.id === id);
            if (!item) return;
            const newQty = parseInt(qty);
            if (newQty < 1) { removeFromCart(id); return; }
            if (newQty > item.stock) { alert('Cannot exceed available stock.'); return; }
            item.quantity = newQty;
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            const empty = document.getElementById('emptyCart');

            if (cart.length === 0) {
                container.innerHTML = '<p id="emptyCart" class="text-gray-400 text-sm">No items added yet.</p>';
                document.getElementById('cartTotal').textContent = 'KES 0.00';
                return;
            }

            let total = 0;
            container.innerHTML = cart.map(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                return `
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">${item.productName}</p>
                            <p class="text-xs text-gray-500">${item.size} | ${item.colour}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" value="${item.quantity}" min="1" max="${item.stock}"
                                onchange="updateQuantity(${item.id}, this.value)"
                                class="w-12 text-center border-gray-300 rounded text-xs">
                            <span class="text-xs font-bold text-green-600 w-20 text-right">KES ${subtotal.toFixed(2)}</span>
                            <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 text-xs">✕</button>
                        </div>
                    </div>`;
            }).join('');

            document.getElementById('cartTotal').textContent = `KES ${total.toFixed(2)}`;
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Please add items to the cart first.');
                return;
            }

            const form = document.getElementById('checkoutForm');
            document.getElementById('formCustomerId').value = document.getElementById('customerSelect').value;
            document.getElementById('formPaymentMethod').value = document.getElementById('paymentMethod').value;

            const itemsContainer = document.getElementById('formItems');
            itemsContainer.innerHTML = cart.map((item, index) => `
                <input type="hidden" name="items[${index}][variant_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            `).join('');

            form.submit();
        }
    </script>
</x-app-layout>