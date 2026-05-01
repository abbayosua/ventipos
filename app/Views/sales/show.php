<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Sale: <?= e($sale->invoice_no) ?></h3>
    <div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <a href="<?= baseUrl('sales') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Items</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Discount</th><th class="text-end">Tax</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item->product_name) ?></td>
                            <td class="text-center"><?= $item->quantity ?></td>
                            <td class="text-end"><?= formatMoney($item->price) ?></td>
                            <td class="text-end"><?= $item->discount_amount > 0 ? formatMoney($item->discount_amount) : '-' ?></td>
                            <td class="text-end"><?= $item->tax_amount > 0 ? formatMoney($item->tax_amount) : '-' ?></td>
                            <td class="text-end fw-bold"><?= formatMoney($item->subtotal) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5" class="text-end">Subtotal</td><td class="text-end"><?= formatMoney($sale->subtotal) ?></td></tr>
                        <?php if ($sale->discount_amount > 0): ?>
                            <tr><td colspan="5" class="text-end text-danger">Discount</td><td class="text-end">-<?= formatMoney($sale->discount_amount) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($sale->tax_amount > 0): ?>
                            <tr><td colspan="5" class="text-end">Tax</td><td class="text-end"><?= formatMoney($sale->tax_amount) ?></td></tr>
                        <?php endif; ?>
                        <tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end"><?= formatMoney($sale->total) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Details</div>
            <div class="card-body small">
                <div class="mb-1"><strong>Invoice:</strong> <?= e($sale->invoice_no) ?></div>
                <div class="mb-1"><strong>Date:</strong> <?= formatDate($sale->created_at, 'd M Y H:i') ?></div>
                <div class="mb-1"><strong>Outlet:</strong> <?= e($sale->outlet_name) ?></div>
                <div class="mb-1"><strong>Cashier:</strong> <?= e($sale->user_name) ?></div>
                <div class="mb-1"><strong>Customer:</strong> <?= e($sale->customer_name ?? 'Walk-in') ?></div>
                <div class="mb-1"><strong>Payment:</strong> <?= e($sale->payment_method) ?></div>
                <div class="mb-1"><strong>Status:</strong>
                    <?php if ($sale->payment_status === 'paid'): ?>
                        <span class="badge bg-success">Paid</span>
                    <?php elseif ($sale->payment_status === 'partial'): ?>
                        <span class="badge bg-warning">Partial</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Unpaid</span>
                    <?php endif; ?>
                </div>
                <?php if ($sale->notes): ?>
                    <div class="mb-1"><strong>Notes:</strong> <?= e($sale->notes) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($payments)): ?>
        <div class="card">
            <div class="card-header">Payments</div>
            <div class="card-body small">
                <?php foreach ($payments as $p): ?>
                    <div class="d-flex justify-content-between">
                        <span><?= e($p->method) ?></span>
                        <span><?= formatMoney($p->amount) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
