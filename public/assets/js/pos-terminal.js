(function () {
    const data = window.PosTerminalData || { products: [] };
    const productMap = new Map();
    const products = [];
    data.products.forEach((product) => {
        const normalised = Object.assign({}, product, {
            price: Number(product.price) || 0,
            vat_rate: Number(product.vat_rate) || 0,
        });
        productMap.set(String(product.id), normalised);
        products.push(normalised);
    });

    const cartItemsEl = document.getElementById('pos-cart-items');
    const cartEmptyEl = document.getElementById('pos-cart-empty');
    const cartHiddenEl = document.getElementById('pos-cart-hidden');
    const subtotalEl = document.getElementById('pos-cart-subtotal');
    const vatEl = document.getElementById('pos-cart-vat');
    const totalEl = document.getElementById('pos-cart-total');
    const searchInput = document.getElementById('pos-product-search');
    const searchButton = document.getElementById('pos-search-button');
    if (!cartItemsEl || !cartHiddenEl || !subtotalEl || !vatEl || !totalEl) {
        return;
    }
    const cart = new Map();

    function addToCart(productId) {
        const product = productMap.get(String(productId));
        if (!product) {
            return;
        }
        const existing = cart.get(String(productId)) || { qty: 0, product };
        existing.qty += 1;
        cart.set(String(productId), existing);
        renderCart();
    }

    function updateQuantity(productId, delta) {
        const entry = cart.get(String(productId));
        if (!entry) {
            return;
        }
        entry.qty += delta;
        if (entry.qty <= 0) {
            cart.delete(String(productId));
        } else {
            cart.set(String(productId), entry);
        }
        renderCart();
    }

    function renderCart() {
        cartItemsEl.innerHTML = '';
        cartHiddenEl.innerHTML = '';
        let subtotal = 0;
        let vat = 0;

        if (cartEmptyEl) {
            cartEmptyEl.style.display = cart.size === 0 ? 'block' : 'none';
        }

        Array.from(cart.values()).forEach((entry, index) => {
            const product = entry.product;
            const lineSubtotal = product.price * entry.qty;
            const lineVat = lineSubtotal * product.vat_rate;
            subtotal += lineSubtotal;
            vat += lineVat;

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between border border-slate-200 rounded px-2 py-1 text-sm';
            row.innerHTML = `
                <div>
                    <p class="font-semibold text-prussian">${product.name}</p>
                    <p class="text-xs text-oxford">\u00A3${product.price.toFixed(2)} · Qty ${entry.qty}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn-secondary text-xs px-2 py-1" data-action="qty" data-product="${product.id}" data-delta="-1">-</button>
                    <button type="button" class="btn-secondary text-xs px-2 py-1" data-action="qty" data-product="${product.id}" data-delta="1">+</button>
                    <span class="font-semibold text-prussian">\u00A3${(lineSubtotal + lineVat).toFixed(2)}</span>
                </div>
            `;
            cartItemsEl.appendChild(row);

            cartHiddenEl.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="items[${index}][product_id]" value="${product.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${entry.qty}">
            `);
        });

        subtotalEl.textContent = `\u00A3${subtotal.toFixed(2)}`;
        vatEl.textContent = `\u00A3${vat.toFixed(2)}`;
        totalEl.textContent = `\u00A3${(subtotal + vat).toFixed(2)}`;
    }

    function handleSearch() {
        if (!searchInput) {
            return;
        }
        const term = searchInput.value.trim().toLowerCase();
        if (!term) {
            return;
        }
        const match = products.find((product) => {
            const sku = product.sku ? product.sku.toLowerCase() : '';
            return product.name.toLowerCase().includes(term) || (sku && sku === term);
        });
        if (match) {
            addToCart(match.id);
            searchInput.value = '';
        }
    }

    document.querySelectorAll('.pos-product-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const productId = button.getAttribute('data-product');
            addToCart(productId);
        });
    });

    if (cartItemsEl) {
        cartItemsEl.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.dataset.action === 'qty') {
                const productId = target.dataset.product;
                const delta = parseInt(target.dataset.delta || '0', 10);
                updateQuantity(productId, delta);
            }
        });
    }

    if (searchButton) {
        searchButton.addEventListener('click', handleSearch);
    }
    if (searchInput) {
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                handleSearch();
            }
        });
    }
})();
