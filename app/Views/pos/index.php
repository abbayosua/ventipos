<?php
$productsJs = json_encode($products);
$customersJs = json_encode($customers);
$currencySymbol = e($currencySymbol);
?>

<div class="pos-container p-2" id="posApp">
    <!-- Products Panel -->
    <div class="pos-products">
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

        <div class="px-2 py-1 border-bottom">
            <select id="customerSelect" class="form-select form-select-sm">
                <option value="">Walk-in Customer</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c->id ?>"><?= e($c->name) ?> (<?= e($c->phone) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pos-cart-items p-2" id="cartItems">
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-plus fs-1"></i>
                <p class="mt-2">Add products to start selling</p>
            </div>
        </div>

        <!-- Inline Checkout Footer -->
        <div class="card-footer bg-white" id="cartFooter">
            <div class="d-flex justify-content-between small mb-1">
                <span>Subtotal</span>
                <span id="cartSubtotal"><?= $currencySymbol ?>0.00</span>
            </div>

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

            <!-- Payment Section -->
            <div id="paymentSection" class="d-none">
                <hr class="my-2">
                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="text" id="notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                    </div>
                </div>

                <!-- Quick Amount Buttons -->
                <div class="d-flex gap-1 mb-2 flex-wrap" id="quickAmounts">
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="500">500</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="1000">1k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="2000">2k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="5000">5k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="10000">10k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="20000">20k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="50000">50k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="100000">100k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" id="btnExact">Exact</button>
                </div>

                <div class="row g-1 mb-1 align-items-center">
                    <div class="col-5"><label class="form-label small mb-0">Amount Paid</label></div>
                    <div class="col-7">
                        <input type="number" id="paidAmount" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                    </div>
                </div>

                <div id="changeRow" class="d-flex justify-content-between fw-bold small d-none">
                    <span>Change Due</span>
                    <span id="changeDisplay" class="text-success"></span>
                </div>

                <button class="btn btn-success w-100 mt-2" id="completeSaleBtn" disabled>
                    <i class="bi bi-check-lg"></i> Complete Sale
                </button>
                <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()" disabled id="clearCartBtn">
                    <i class="bi bi-trash"></i> Clear Cart
                </button>
            </div>

            <!-- Empty cart: show this -->
            <div id="emptyCartActions">
                <button class="btn btn-primary w-100 mt-2" id="checkoutBtn" disabled>
                    <i class="bi bi-cash"></i> Checkout
                </button>
                <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()" disabled id="clearCartBtnEmpty">
                    <i class="bi bi-trash"></i> Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Toast -->
<div id="receiptToast" class="toast position-fixed bottom-0 end-0 m-3 d-none" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-success text-white">
        <strong class="me-auto"><i class="bi bi-check-circle"></i> Sale Complete</strong>
        <button type="button" class="btn-close btn-close-white" onclick="resetPOS()"></button>
    </div>
    <div class="toast-body" id="receiptBody"></div>
</div>

<script>
const products = <?= $productsJs ?>;
const customers = <?= $customersJs ?>;
const currencySymbol = '<?= $currencySymbol ?>';
const baseUrl = '<?= baseUrl() ?>';
</script>
