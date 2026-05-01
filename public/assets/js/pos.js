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
const paymentSection = document.getElementById('paymentSection');
const emptyCartActions = document.getElementById('emptyCartActions');
const completeSaleBtn = document.getElementById('completeSaleBtn');
const paidAmount = document.getElementById('paidAmount');
const changeDisplay = document.getElementById('changeDisplay');
const changeRow = document.getElementById('changeRow');

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

    // Quick amount buttons
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.id === 'btnExact') {
                const total = getTotal();
                paidAmount.value = total.toFixed(2);
            } else {
                const val = parseFloat(this.dataset.amount);
                paidAmount.value = val.toFixed(2);
            }
            updateChange();
        });
    });

    // Paid amount input
    if (paidAmount) {
        paidAmount.addEventListener('input', updateChange);
        paidAmount.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                completeSale();
            }
        });
    }

    // Complete sale button
    if (completeSaleBtn) {
        completeSaleBtn.addEventListener('click', completeSale);
    }

    // Focus product search on keypress anywhere
    document.addEventListener('keydown', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'Escape') resetPOS();
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            searchInput.focus();
        }
    });
});

function getTotal() {
    const total = parseFloat(cartTotal.textContent.replace(/[^0-9.-]/g, ''));
    return isNaN(total) ? 0 : total;
}

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

function renderCart() {
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-cart-plus fs-1"></i><p class="mt-2">Add products to start selling</p></div>';
        checkoutBtn.disabled = true;
        if (clearCartBtn) clearCartBtn.disabled = true;
        const clearBtnEmpty = document.getElementById('clearCartBtnEmpty');
        if (clearBtnEmpty) clearBtnEmpty.disabled = true;
        paymentSection.classList.add('d-none');
        emptyCartActions.classList.remove('d-none');
        updateTotals();
        return;
    }

    checkoutBtn.disabled = false;
    if (clearCartBtn) clearCartBtn.disabled = false;
    const clearBtnEmpty = document.getElementById('clearCartBtnEmpty');
    if (clearBtnEmpty) clearBtnEmpty.disabled = false;

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
                    <select class="form-select form-select-sm" style="width:auto;" onchange="itemDiscount(${index}, this.value)">
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

function itemDiscount(index, type) {
    cart[index].discount_type = type || null;
    if (!type) cart[index].discount_value = 0;
    renderCart();
}

function itemDiscountValue(index, val) {
    cart[index].discount_value = parseFloat(val) || 0;
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

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

    // Toggle payment section visibility
    if (cart.length > 0) {
        paymentSection.classList.remove('d-none');
        emptyCartActions.classList.add('d-none');
    } else {
        paymentSection.classList.add('d-none');
        emptyCartActions.classList.remove('d-none');
    }
}

function updateChange() {
    const total = getTotal();
    const paid = parseFloat(paidAmount.value) || 0;
    const change = paid - total;

    if (paid > 0) {
        changeRow.classList.remove('d-none');
        if (paid >= total) {
            changeDisplay.textContent = `${currencySymbol}${change.toFixed(2)}`;
            changeDisplay.classList.remove('text-danger');
            changeDisplay.classList.add('text-success');
            completeSaleBtn.disabled = false;
        } else {
            changeDisplay.textContent = `${currencySymbol}${Math.abs(change).toFixed(2)} (${langShort})`;
            changeDisplay.classList.remove('text-success');
            changeDisplay.classList.add('text-danger');
            completeSaleBtn.disabled = true;
        }
    } else {
        changeRow.classList.add('d-none');
        completeSaleBtn.disabled = true;
    }
}

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

function clearCart() {
    if (cart.length === 0) return;
    if (!confirm('Clear the entire cart?')) return;
    cart = [];
    renderCart();
}

async function completeSale() {
    if (cart.length === 0) return;

    const total = getTotal();
    const paid = parseFloat(paidAmount.value) || 0;
    if (paid < total) {
        alert('Amount paid is less than the total!');
        return;
    }

    completeSaleBtn.disabled = true;
    completeSaleBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

    const items = cart.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        discount_type: item.discount_type || null,
        discount_value: item.discount_value || 0,
    }));

    const formData = new FormData();
    formData.append('items', JSON.stringify(items));
    formData.append('payment_method', document.getElementById('paymentMethod').value);
    formData.append('paid_amount', paid.toString());
    formData.append('customer_id', document.getElementById('customerSelect').value || '');
    formData.append('discount_type', orderDiscountType || '');
    formData.append('discount_value', (orderDiscountValue || 0).toString());
    formData.append('notes', document.getElementById('notes').value || '');

    try {
        const response = await fetch(baseUrl + 'pos/checkout', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.error) {
            alert('Error: ' + data.error);
            completeSaleBtn.disabled = false;
            completeSaleBtn.innerHTML = '<i class="bi bi-check-lg"></i> Complete Sale';
            return;
        }

        // Show receipt overlay
        const overlay = document.getElementById('receiptOverlay');
        const body = document.getElementById('receiptBody');
        body.innerHTML = `
            <div class="text-center mb-3 fw-bold fs-5">${data.invoice_no}</div>
            ${cart.map(item => `<div class="d-flex justify-content-between small mb-1"><span>${escapeHtml(item.name)} × ${item.quantity}</span><span>${currencySymbol}${(item.price * item.quantity).toFixed(2)}</span></div>`).join('')}
            <hr>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>${currencySymbol}${data.total.toFixed(2)}</span></div>
            <div class="d-flex justify-content-between small"><span>Paid</span><span>${currencySymbol}${paid.toFixed(2)}</span></div>
            <div class="d-flex justify-content-between small text-success"><span>Change</span><span>${currencySymbol}${data.change.toFixed(2)}</span></div>
        `;
        overlay.classList.remove('d-none');
    } catch (err) {
        alert('Checkout failed: ' + err.message);
        completeSaleBtn.disabled = false;
        completeSaleBtn.innerHTML = '<i class="bi bi-check-lg"></i> Complete Sale';
    }
}

function printReceipt() {
    const overlay = document.getElementById('receiptOverlay');
    const printContent = document.getElementById('printReceiptContent');
    const body = document.getElementById('receiptBody');

    // Copy receipt data with proper formatting
    const now = new Date();
    const dateStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Get invoice and totals from the receipt body
    const invoiceMatch = body.innerHTML.match(/(INV-\S+)/);
    const invoiceNo = invoiceMatch ? invoiceMatch[1] : '-';

    printContent.innerHTML = `
        <div class="receipt-store">${escapeHtml(storeName)}</div>
        <div class="receipt-address">${escapeHtml(storeAddress)}</div>
        <div class="receipt-line">${'='.repeat(32)}</div>
        <div class="receipt-row"><span>Invoice</span><span>${invoiceNo}</span></div>
        <div class="receipt-row"><span>Date</span><span>${dateStr}</span></div>
        <div class="receipt-line">${'-'.repeat(32)}</div>
        ${cart.map(item => `
            <div class="receipt-item">
                <div class="receipt-item-name">${escapeHtml(item.name)}</div>
                <div class="receipt-item-qty">${item.quantity} × ${currencySymbol}${item.price.toFixed(2)}</div>
                <div class="receipt-item-total">${currencySymbol}${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `).join('')}
        <div class="receipt-line">${'-'.repeat(32)}</div>
        <div class="receipt-row fw-bold"><span>Total</span><span>${cartTotal.textContent}</span></div>
        <div class="receipt-row"><span>Paid</span><span>${currencySymbol}${paidAmount.value}</span></div>
        <div class="receipt-row text-success"><span>Change</span><span>${changeDisplay.textContent}</span></div>
        <div class="receipt-line">${'='.repeat(32)}</div>
        <div class="receipt-footer">${escapeHtml(langThankYou)}</div>
    `;

    overlay.classList.add('d-none');
    const printEl = document.getElementById('printReceipt');
    printEl.classList.remove('d-none');

    setTimeout(() => {
        window.print();
        printEl.classList.add('d-none');
        overlay.classList.remove('d-none');
    }, 100);
}

function resetPOS() {
    cart = [];
    orderDiscountType = '';
    orderDiscountValue = 0;
    document.getElementById('orderDiscountType').value = '';
    document.getElementById('orderDiscountValue').value = '';
    document.getElementById('orderDiscountValue').disabled = true;
    paidAmount.value = '';
    changeRow.classList.add('d-none');
    document.getElementById('notes').value = '';
    document.getElementById('receiptOverlay').classList.add('d-none');
    renderCart();
}

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
