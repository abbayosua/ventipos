<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('stock.movements_for', ['name' => $product->name]) ?></h3>
    <a href="<?= baseUrl('stock') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th><?= __('common.date') ?></th><th><?= __('stock.type') ?></th><th class="text-end"><?= __('stock.quantity') ?></th><th><?= __('stock.reference') ?></th><th><?= __('stock.notes') ?></th><th><?= __('stock.by') ?></th></tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): ?>
                    <tr>
                        <td><?= formatDate($m->created_at, 'd M H:i') ?></td>
                        <td>
                            <?php if ($m->type === 'in'): ?>
                                <span class="badge bg-success"><?= __('stock.type_in') ?></span>
                            <?php elseif ($m->type === 'out'): ?>
                                <span class="badge bg-danger"><?= __('stock.type_out') ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning"><?= __('stock.type_adjustment') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold"><?= $m->quantity ?></td>
                        <td><?= $m->reference_type ? e(ucfirst($m->reference_type) . ' #' . $m->reference_id) : '-' ?></td>
                        <td class="text-muted"><?= e($m->notes ?? '-') ?></td>
                        <td><?= e($m->user_name ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
