<?php
$productsJs = json_encode($products);
$customersJs = json_encode($customers);
$currencySymbol = e($currencySymbol);
?>

<div class="pos-container p-2" id="posApp">
    <!-- Products Panel -->
    <div class="pos-products">
        <!-- Search & Filters -->
        <div class="row g-2 mb-2">
            <div class="col-md-5">
                <input type="text" id="posSearch" class="form-control form-control-sm"
                       placeholder="Search by name, SKU or barcode..." autofocus>
            </div>
            <div class="col-md-3">
                <select id="posCategory" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>"><?= e($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" id="posBarcode" class="form-control form-control-sm"
                       placeholder="Scan barcode...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="clearFilters()">Clear</button>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row g-2" id="productGrid">
            <?php foreach ($products as $p): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 product-card"
                     data-id="<?= $p->id ?>"
                     data-name="<?= e($p->name) ?>"
                     data-price="<?= $p->selling_price ?>"
                     data-tax="<?= $p->tax_rate ?>"
                     data-stock="<?= $p->stock_qty ?>"
                     data-category="<?= $p->category_id ?? '' ?>"
                     data-sku="<?= e($p->sku) ?>"
                     data-barcode="<?= e($p->barcode) ?>">
                    <div class="card product-btn h-100 <?= $p->stock_qty <= 0 ? 'opacity-50' : '' ?>"
                         onclick="<?= $p->stock_qty > 0 ? "addToCart({$p->id})" : '' ?>">
                        <div class="card-body p-2 text-center">
                            <div class="mb-1 fw-bold small"><?= e($p->name) ?></div>
                            <div class="text-primary fw-bold"><?= $currencySymbol ?><?= number_format($p->selling_price, 2) ?></div>
                            <small class="text-muted">Stock: <?= $p->stock_qty ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart card shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-cart"></i> Cart</strong>
            <span id="cartCount" class="badge bg-primary">0</span>
        </div>

        <!-- Customer Select -->
        <div class="px-2 py-1 border-bottom">
            <select id="customerSelect" class="form-select form-select-sm">
                <option value="">Walk-in Customer</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c->id ?>"><?= e($c->name) ?> (<?= e($c->phone) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Cart Items -->
        <div class="pos-cart-items p-2" id="cartItems">
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-plus fs-1"></i>
                <p class="mt-2">Add products to start selling</p>
            </div>
        </div>

        <!-- Totals -->
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between small mb-1">
                <span>Subtotal</span>
                <span id="cartSubtotal"><?= $currencySymbol ?>0.00</span>
            </div>

            <!-- Order Discount -->
            <div class="row g-1 mb-1">
                <div class="col-5">
                    <select id="orderDiscountType" class="form-select form-select-sm">
                        <option value="">No Discount</option>
                        <option value="percentage">% Discount</option>
                        <option value="fixed">Fixed Discount</option>
                    </select>
                </div>
                <div class="col-4">
                    <input type="number" id="orderDiscountValue" class="form-control form-control-sm" min="0" step="0.01"
                           placeholder="Amount" disabled>
                </div>
                <div class="col-3 d-flex align-items-center small text-danger" id="orderDiscountDisplay"></div>
            </div>

            <div class="d-flex justify-content-between small mb-1">
                <span>Tax</span>
                <span id="cartTax"><?= $currencySymbol ?>0.00</span>
            </div>
            <hr class="my-1">
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total</span>
                <span id="cartTotal"><?= $currencySymbol ?>0.00</span>
            </div>
            <button class="btn btn-primary w-100 mt-2" onclick="openCheckout()" id="checkoutBtn" disabled>
                <i class="bi bi-cash"></i> Checkout
            </button>
            <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()" disabled id="clearCartBtn">
                <i class="bi bi-trash"></i> Clear Cart
            </button>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="checkoutForm">
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total:</span>
                        <span class="fw-bold fs-4" id="checkoutTotal"><?= $currencySymbol ?>0.00</span>
                    </div>
                    <input type="hidden" name="items" id="checkoutItems">
                    <input type="hidden" name="customer_id" id="checkoutCustomerId">
                    <input type="hidden" name="discount_type" id="checkoutDiscountType">
                    <input type="hidden" name="discount_value" id="checkoutDiscountValue">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" name="paid_amount" class="form-control form-control-lg" step="0.01" min="0"
                               id="paidAmount" required>
                    </div>
                    <div id="changeDisplay" class="text-end text-success fw-bold d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitCheckout">
                        <i class="bi bi-check-lg"></i> Complete Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Complete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetPOS()"></button>
            </div>
            <div class="modal-body text-center" id="receiptBody">
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetPOS()">New Sale</button>
            </div>
        </div>
    </div>
</div>

<script>
const products = <?= $productsJs ?>;
const customers = <?= $customersJs ?>;
const currencySymbol = '<?= $currencySymbol ?>';
const baseUrl = '<?= baseUrl() ?>';
</script>
