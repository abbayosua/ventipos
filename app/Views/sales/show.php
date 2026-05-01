<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('sales.detail') ?>: <?= e($sale->invoice_no) ?></h3>
    <div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> <?= __('common.print') ?></button>
        <a href="<?= baseUrl('sales') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><?= __('sales.items') ?></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th><?= __('products.name') ?></th><th class="text-center"><?= __('common.qty') ?></th><th class="text-end"><?= __('products.selling_price') ?></th><th class="text-end"><?= __('common.discount') ?></th><th class="text-end"><?= __('common.tax') ?></th><th class="text-end"><?= __('common.subtotal') ?></th></tr></thead>
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
                        <tr><td colspan="5" class="text-end"><?= __('common.subtotal') ?></td><td class="text-end"><?= formatMoney($sale->subtotal) ?></td></tr>
                        <?php if ($sale->discount_amount > 0): ?>
                            <tr><td colspan="5" class="text-end text-danger"><?= __('common.discount') ?></td><td class="text-end">-<?= formatMoney($sale->discount_amount) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($sale->tax_amount > 0): ?>
                            <tr><td colspan="5" class="text-end"><?= __('common.tax') ?></td><td class="text-end"><?= formatMoney($sale->tax_amount) ?></td></tr>
                        <?php endif; ?>
                        <tr class="fw-bold"><td colspan="5" class="text-end"><?= __('common.total') ?></td><td class="text-end"><?= formatMoney($sale->total) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><?= __('sales.detail') ?></div>
            <div class="card-body small">
                <div class="mb-1"><strong><?= __('sales.invoice') ?>:</strong> <?= e($sale->invoice_no) ?></div>
                <div class="mb-1"><strong><?= __('common.date') ?>:</strong> <?= formatDate($sale->created_at, 'd M Y H:i') ?></div>
                <div class="mb-1"><strong><?= __('sales.outlet') ?>:</strong> <?= e($sale->outlet_name) ?></div>
                <div class="mb-1"><strong><?= __('sales.cashier') ?>:</strong> <?= e($sale->user_name) ?></div>
                <div class="mb-1"><strong><?= __('sales.customer') ?>:</strong> <?= e($sale->customer_name ?? '-') ?></div>
                <div class="mb-1"><strong><?= __('sales.payment') ?>:</strong> <?= e($sale->payment_method) ?></div>
                <div class="mb-1"><strong><?= __('common.status') ?>:</strong>
                    <?php if ($sale->payment_status === 'paid'): ?>
                        <span class="badge bg-success"><?= __('sales.status_paid') ?></span>
                    <?php elseif ($sale->payment_status === 'partial'): ?>
                        <span class="badge bg-warning"><?= __('sales.status_partial') ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?= __('sales.status_unpaid') ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($sale->notes): ?>
                    <div class="mb-1"><strong><?= __('common.notes') ?>:</strong> <?= e($sale->notes) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($payments)): ?>
        <div class="card">
            <div class="card-header"><?= __('sales.payments') ?></div>
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
