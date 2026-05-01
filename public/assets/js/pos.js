// State
let cart = [];
let orderDiscountType = '';
let orderDiscountValue = 0;

// DOM refs
const grid = document.getElementById('productGrid');
const searchInput = document.getElementById('posSearch');
const categorySelect = document.getElementById('posCategory');
const barcodeInput = document.getElementById('posBarcode');
const cartItems = document.getElementById('cartItems');
const cartCount = document.getElementById('cartCount');
const cartSubtotal = document.getElementById('cartSubtotal');
const cartTax = document.getElementById('cartTax');
const cartTotal = document.getElementById('cartTotal');
const checkoutBtn = document.getElementById('checkoutBtn');
const clearCartBtn = document.getElementById('clearCartBtn');

// Init
document.addEventListener('DOMContentLoaded', function () {
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterProducts, 300));
    }
    if (categorySelect) {
        categorySelect.addEventListener('change', filterProducts);
    }
    if (barcodeInput) {
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim();
                if (barcode) {
                    const product = products.find(p => p.barcode === barcode);
                    if (product) {
                        addToCart(product.id);
                        this.value = '';
                    } else {
                        alert('Product not found: ' + barcode);
                    }
                }
            }
        });
    }

    // Order discount
    const discountType = document.getElementById('orderDiscountType');
    const discountValue = document.getElementById('orderDiscountValue');
    if (discountType) {
        discountType.addEventListener('change', function () {
            discountValue.disabled = !this.value;
            if (!this.value) discountValue.value = '';
            orderDiscountType = this.value;
            orderDiscountValue = 0;
            renderCart();
        });
    }
    if (discountValue) {
        discountValue.addEventListener('input', function () {
            orderDiscountValue = parseFloat(this.value) || 0;
            renderCart();
        });
    }

    // Checkout form
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        const paidInput = document.getElementById('paidAmount');
        paidInput.addEventListener('input', updateChange);
        checkoutForm.addEventListener('submit', submitCheckout);
    }
});

// Add to cart
function addToCart(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    if (product.stock_qty <= 0) return;

    const existing = cart.find(c => c.product_id == productId);
    if (existing) {
        if (existing.quantity >= product.stock_qty) {
            alert('Insufficient stock!');
            return;
        }
        existing.quantity++;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: parseFloat(product.selling_price),
            tax_rate: parseFloat(product.tax_rate),
            quantity: 1,
            discount_type: null,
            discount_value: 0,
        });
    }

    renderCart();
}

// Render cart
function renderCart() {
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-cart-plus fs-1"></i><p class="mt-2">Add products to start selling</p></div>';
        checkoutBtn.disabled = true;
        clearCartBtn.disabled = true;
        updateTotals();
        return;
    }

    checkoutBtn.disabled = false;
    clearCartBtn.disabled = false;

    let html = '';
    cart.forEach((item, index) => {
        const lineTotal = item.price * item.quantity;
        let itemDiscount = 0;
        if (item.discount_type === 'percentage') {
            itemDiscount = lineTotal * (item.discount_value / 100);
        } else if (item.discount_type === 'fixed') {
            itemDiscount = Math.min(item.discount_value, lineTotal);
        }
        const afterDiscount = lineTotal - itemDiscount;
        const taxAmount = afterDiscount * (item.tax_rate / 100);
        const itemTotal = afterDiscount + taxAmount;

        html += `
            <div class="cart-item small">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="fw-medium">${escapeHtml(item.name)}</div>
                        <div class="text-muted">${currencySymbol}${item.price.toFixed(2)} × ${item.quantity}</div>
                        ${item.discount_type ? `<div class="text-danger small">Discount: ${item.discount_value}${item.discount_type === 'percentage' ? '%' : ''}</div>` : ''}
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">${currencySymbol}${itemTotal.toFixed(2)}</div>
                        <div class="btn-group btn-group-xs">
                            <button class="btn btn-outline-secondary btn-sm py-0 px-1" onclick="updateQty(${index}, -1)">−</button>
                            <span class="px-2">${item.quantity}</span>
                            <button class="btn btn-outline-secondary btn-sm py-0 px-1" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-1 mt-1">
                    <select class="form-select form-select-sm" style="width:auto;" onchange="itemDiscount(${index}, this.value, '${item.discount_type || ''}')">
                        <option value="">No disc</option>
                        <option value="percentage" ${item.discount_type === 'percentage' ? 'selected' : ''}>%</option>
                        <option value="fixed" ${item.discount_type === 'fixed' ? 'selected' : ''}>Fixed</option>
                    </select>
                    <input type="number" class="form-control form-control-sm" style="width:65px;" min="0" step="0.01"
                           placeholder="Val" value="${item.discount_value || ''}"
                           oninput="itemDiscountValue(${index}, this.value)">
                    <button class="btn btn-outline-danger btn-sm py-0 px-1" onclick="removeItem(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    updateTotals();
}

// Update quantity
function updateQty(index, delta) {
    const product = products.find(p => p.id == cart[index].product_id);
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    } else if (product && cart[index].quantity > product.stock_qty) {
        cart[index].quantity = product.stock_qty;
        alert('Insufficient stock!');
    }
    renderCart();
}

// Item discount
function itemDiscount(index, type) {
    cart[index].discount_type = type || null;
    if (!type) cart[index].discount_value = 0;
    renderCart();
}

function itemDiscountValue(index, val) {
    cart[index].discount_value = parseFloat(val) || 0;
    renderCart();
}

// Remove item
function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

// Update totals
function updateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let itemCount = 0;

    cart.forEach(item => {
        const lineTotal = item.price * item.quantity;
        let itemDiscount = 0;
        if (item.discount_type === 'percentage') {
            itemDiscount = lineTotal * (item.discount_value / 100);
        } else if (item.discount_type === 'fixed') {
            itemDiscount = Math.min(item.discount_value, lineTotal);
        }
        const afterDiscount = lineTotal - itemDiscount;
        const taxAmount = afterDiscount * (item.tax_rate / 100);
        subtotal += afterDiscount;
        totalTax += taxAmount;
        itemCount += item.quantity;
    });

    let orderDiscount = 0;
    if (orderDiscountType === 'percentage') {
        orderDiscount = subtotal * (orderDiscountValue / 100);
    } else if (orderDiscountType === 'fixed') {
        orderDiscount = Math.min(orderDiscountValue, subtotal);
    }

    const display = document.getElementById('orderDiscountDisplay');
    if (orderDiscount > 0) {
        display.textContent = `−${currencySymbol}${orderDiscount.toFixed(2)}`;
    } else {
        display.textContent = '';
    }

    const total = subtotal - orderDiscount + totalTax;

    cartCount.textContent = itemCount;
    cartSubtotal.textContent = `${currencySymbol}${subtotal.toFixed(2)}`;
    cartTax.textContent = `${currencySymbol}${totalTax.toFixed(2)}`;
    cartTotal.textContent = `${currencySymbol}${total.toFixed(2)}`;
}

// Filter products
function filterProducts() {
    const query = (searchInput.value || '').toLowerCase();
    const catId = categorySelect.value;
    const cards = grid.querySelectorAll('.product-card');

    cards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const sku = (card.dataset.sku || '').toLowerCase();
        const barcode = (card.dataset.barcode || '').toLowerCase();
        const cardCat = card.dataset.category || '';

        const matchesSearch = !query || name.includes(query) || sku.includes(query) || barcode.includes(query);
        const matchesCat = !catId || cardCat === catId;

        card.style.display = (matchesSearch && matchesCat) ? '' : 'none';
    });
}

function clearFilters() {
    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = '';
    filterProducts();
}

// Clear cart
function clearCart() {
    if (cart.length === 0) return;
    if (!confirm('Clear the entire cart?')) return;
    cart = [];
    renderCart();
}

// Open checkout modal
function openCheckout() {
    if (cart.length === 0) return;

    updateTotals();

    const totalEl = document.getElementById('checkoutTotal');
    const subtotal = parseFloat(cartSubtotal.textContent.replace(/[^0-9.-]/g, ''));
    const tax = parseFloat(cartTax.textContent.replace(/[^0-9.-]/g, ''));
    let orderDiscount = 0;
    if (orderDiscountType === 'percentage') {
        orderDiscount = subtotal * (orderDiscountValue / 100);
    } else if (orderDiscountType === 'fixed') {
        orderDiscount = Math.min(orderDiscountValue, subtotal);
    }
    const total = subtotal - orderDiscount + tax;

    totalEl.textContent = `${currencySymbol}${total.toFixed(2)}`;
    document.getElementById('paidAmount').value = total.toFixed(2);
    document.getElementById('paidAmount').min = total.toFixed(2);
    document.getElementById('checkoutItems').value = JSON.stringify(cart.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        discount_type: item.discount_type || null,
        discount_value: item.discount_value || 0,
    })));
    document.getElementById('checkoutCustomerId').value = document.getElementById('customerSelect').value;
    document.getElementById('checkoutDiscountType').value = orderDiscountType || '';
    document.getElementById('checkoutDiscountValue').value = orderDiscountValue || 0;

    const changeDisplay = document.getElementById('changeDisplay');
    changeDisplay.classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    modal.show();
    setTimeout(() => document.getElementById('paidAmount').select(), 300);
}

// Update change
function updateChange() {
    const totalText = document.getElementById('checkoutTotal').textContent.replace(/[^0-9.-]/g, '');
    const total = parseFloat(totalText) || 0;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    const change = paid - total;
    const changeDisplay = document.getElementById('changeDisplay');
    if (paid >= total) {
        changeDisplay.textContent = `Change: ${currencySymbol}${change.toFixed(2)}`;
        changeDisplay.classList.remove('d-none', 'text-danger');
        changeDisplay.classList.add('text-success');
    } else {
        changeDisplay.textContent = `Short: ${currencySymbol}${Math.abs(change).toFixed(2)}`;
        changeDisplay.classList.remove('d-none', 'text-success');
        changeDisplay.classList.add('text-danger');
    }
}

// Submit checkout
async function submitCheckout(e) {
    e.preventDefault();

    const btn = document.getElementById('submitCheckout');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

    const formData = new FormData(document.getElementById('checkoutForm'));

    try {
        const response = await fetch(baseUrl + 'pos/checkout', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.error) {
            alert('Error: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Complete Sale';
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

        // Show receipt
        const receiptBody = document.getElementById('receiptBody');
        receiptBody.innerHTML = `
            <h5>VentiPOS</h5>
            <p class="mb-1">Invoice: ${data.invoice_no}</p>
            <hr>
            ${cart.map(item => `<div class="d-flex justify-content-between"><span>${escapeHtml(item.name)} × ${item.quantity}</span><span>${currencySymbol}${(item.price * item.quantity).toFixed(2)}</span></div>`).join('')}
            <hr>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>${currencySymbol}${data.total.toFixed(2)}</span></div>
            <div class="d-flex justify-content-between"><span>Paid</span><span>${currencySymbol}${(parseFloat(document.getElementById('paidAmount').value) || 0).toFixed(2)}</span></div>
            <div class="d-flex justify-content-between text-success"><span>Change</span><span>${currencySymbol}${data.change.toFixed(2)}</span></div>
            <hr>
            <p class="mb-0 text-muted">Thank you!</p>
        `;

        const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        receiptModal.show();
    } catch (err) {
        alert('Checkout failed: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Complete Sale';
    }
}

// Reset POS
function resetPOS() {
    cart = [];
    orderDiscountType = '';
    orderDiscountValue = 0;
    document.getElementById('orderDiscountType').value = '';
    document.getElementById('orderDiscountValue').value = '';
    document.getElementById('orderDiscountValue').disabled = true;
    renderCart();
}

// Helpers
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
