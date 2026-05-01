<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('sales.title') ?></h3>
    <a href="<?= baseUrl('pos') ?>" class="btn btn-primary btn-sm"><i class="bi bi-cart"></i> <?= __('sales.new_sale') ?></a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><?= __('sales.invoice') ?></th>
                    <th><?= __('sales.customer') ?></th>
                    <th><?= __('sales.items') ?></th>
                    <th class="text-end"><?= __('common.total') ?></th>
                    <th class="text-center"><?= __('common.status') ?></th>
                    <th><?= __('sales.date') ?></th>
                    <th class="text-end"><?= __('common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($sales as $s): ?>
                    <tr>
                        <td class="fw-medium"><?= e($s->invoice_no) ?></td>
                        <td><?= e($s->customer_name ?? '-') ?></td>
                        <td><?= \App\Core\Database::fetch("SELECT COUNT(*) as c FROM sale_items WHERE sale_id = ?", [$s->id])->c ?></td>
                        <td class="text-end"><?= formatMoney($s->total) ?></td>
                        <td class="text-center">
                            <?php if ($s->payment_status === 'paid'): ?>
                                <span class="badge bg-success"><?= __('sales.status_paid') ?></span>
                            <?php elseif ($s->payment_status === 'partial'): ?>
                                <span class="badge bg-warning"><?= __('sales.status_partial') ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= __('sales.status_unpaid') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDate($s->created_at, 'd M H:i') ?></td>
                        <td class="text-end">
                            <a href="<?= baseUrl('sales/' . $s->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
