<?php
$productsJs = json_encode($products);
$customersJs = json_encode($customers);
$currencySymbol = e($currencySymbol);
?>

<div class="pos-container p-2" id="posApp">
    <div class="pos-products">
        <div class="row g-2 mb-2">
            <div class="col-md-5">
                <input type="text" id="posSearch" class="form-control form-control-sm"
                       placeholder="<?= __('pos.search') ?>" autofocus>
            </div>
            <div class="col-md-3">
                <select id="posCategory" class="form-select form-select-sm">
                    <option value=""><?= __('pos.all_categories') ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>"><?= e($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <input type="text" id="posBarcode" class="form-control"
                           placeholder="<?= __('pos.scan_barcode') ?>">
                    <button class="btn btn-outline-secondary" type="button" onclick="openBarcodeScanner('pos')" title="Scan with camera">
                        <i class="bi bi-camera"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="clearFilters()"><?= __('common.clear') ?></button>
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
                            <small class="text-muted"><?= __('pos.stock') ?>: <?= $p->stock_qty ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-cart card shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-cart"></i> <?= __('pos.cart') ?></strong>
            <span id="cartCount" class="badge bg-primary">0</span>
        </div>

        <div class="px-2 py-1 border-bottom">
            <select id="customerSelect" class="form-select form-select-sm">
                <option value=""><?= __('pos.walkin_customer') ?></option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c->id ?>"><?= e($c->name) ?> (<?= e($c->phone) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pos-cart-items p-2" id="cartItems">
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-plus fs-1"></i>
                <p class="mt-2"><?= __('pos.add_product_hint') ?></p>
            </div>
        </div>

        <div class="card-footer bg-white" id="cartFooter">
            <div class="d-flex justify-content-between small mb-1">
                <span><?= __('common.subtotal') ?></span>
                <span id="cartSubtotal"><?= $currencySymbol ?>0.00</span>
            </div>

            <div class="row g-1 mb-1">
                <div class="col-5">
                    <select id="orderDiscountType" class="form-select form-select-sm">
                        <option value=""><?= __('pos.no_discount') ?></option>
                        <option value="percentage"><?= __('pos.pct_discount') ?></option>
                        <option value="fixed"><?= __('pos.fixed_discount') ?></option>
                    </select>
                </div>
                <div class="col-4">
                    <input type="number" id="orderDiscountValue" class="form-control form-control-sm" min="0" step="0.01"
                           placeholder="<?= __('common.amount') ?>" disabled>
                </div>
                <div class="col-3 d-flex align-items-center small text-danger" id="orderDiscountDisplay"></div>
            </div>

            <div class="d-flex justify-content-between small mb-1">
                <span><?= __('common.tax') ?></span>
                <span id="cartTax"><?= $currencySymbol ?>0.00</span>
            </div>
            <hr class="my-1">
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span><?= __('common.total') ?></span>
                <span id="cartTotal"><?= $currencySymbol ?>0.00</span>
            </div>

            <div id="paymentSection" class="d-none">
                <hr class="my-2">
                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash"><?= __('pos.cash') ?></option>
                            <option value="card"><?= __('pos.card') ?></option>
                            <option value="transfer"><?= __('pos.transfer') ?></option>
                            <option value="other"><?= __('pos.other') ?></option>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="text" id="notes" class="form-control form-control-sm" placeholder="<?= __('common.notes') ?>">
                    </div>
                </div>

                <div class="d-flex gap-1 mb-2 flex-wrap" id="quickAmounts">
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="500">500</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="1000">1k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="2000">2k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="5000">5k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="10000">10k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="20000">20k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="50000">50k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" data-amount="100000">100k</button>
                    <button class="btn btn-outline-secondary btn-sm qty-btn" id="btnExact"><?= __('pos.exact') ?></button>
                </div>

                <div class="row g-1 mb-1 align-items-center">
                    <div class="col-5"><label class="form-label small mb-0"><?= __('pos.amount_paid') ?></label></div>
                    <div class="col-7">
                        <input type="number" id="paidAmount" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                    </div>
                </div>

                <div id="changeRow" class="d-flex justify-content-between fw-bold small d-none">
                    <span><?= __('pos.change_due') ?></span>
                    <span id="changeDisplay" class="text-success"></span>
                </div>

                <button class="btn btn-success w-100 mt-2" id="completeSaleBtn" disabled>
                    <i class="bi bi-check-lg"></i> <?= __('pos.complete_sale') ?>
                </button>
                <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()" disabled id="clearCartBtn">
                    <i class="bi bi-trash"></i> <?= __('pos.clear_cart') ?>
                </button>
            </div>

            <div id="emptyCartActions">
                <button class="btn btn-primary w-100 mt-2" id="checkoutBtn" disabled>
                    <i class="bi bi-cash"></i> <?= __('pos.complete_sale') ?>
                </button>
                <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()" disabled id="clearCartBtnEmpty">
                    <i class="bi bi-trash"></i> <?= __('pos.clear_cart') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="scannerOverlay" class="scanner-overlay d-none">
    <div class="scanner-modal">
        <div class="scanner-modal-header">
            <span>Barcode Scanner</span>
            <button type="button" class="btn-close btn-close-white" onclick="closeBarcodeScanner()"></button>
        </div>
        <div class="scanner-modal-body">
            <div id="scannerContainer"></div>
            <div id="scannerResult" class="scanner-result d-none"></div>
        </div>
        <div class="scanner-modal-footer">
            <button class="btn btn-secondary btn-sm w-100" onclick="closeBarcodeScanner()">Cancel</button>
        </div>
    </div>
</div>

<div id="receiptOverlay" class="receipt-overlay d-none">
    <div class="receipt-modal">
        <div class="receipt-modal-header">
            <i class="bi bi-check-circle"></i> <?= __('pos.sale_complete') ?>
        </div>
        <div class="receipt-modal-body" id="receiptBody"></div>
        <div class="receipt-modal-footer">
            <button class="btn btn-outline-primary" onclick="printReceipt()"><i class="bi bi-printer"></i> <?= __('common.print') ?></button>
            <button class="btn btn-success" onclick="resetPOS()"><i class="bi bi-plus-lg"></i> <?= __('pos.new_sale') ?></button>
        </div>
    </div>
</div>

<!-- Print-only receipt -->
<div id="printReceipt" class="d-none">
    <div class="print-receipt-content" id="printReceiptContent"></div>
</div>

<script>
const products = <?= $productsJs ?>;
const customers = <?= $customersJs ?>;
const currencySymbol = '<?= $currencySymbol ?>';
const baseUrl = '<?= baseUrl() ?>';
const langShort = '<?= __('pos.short') ?>';
const langThankYou = <?= json_encode(__('pos.thank_you')) ?>;
const storeName = <?= json_encode(\App\Core\Session::get('company_name', config('app.name'))) ?>;
const storeAddress = <?= json_encode(\App\Core\Session::get('company_address', '')) ?>;
</script>
