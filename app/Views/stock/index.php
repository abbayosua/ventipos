<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('stock.title') ?></h3>
    <a href="<?= baseUrl('stock/adjustment') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> <?= __('stock.adjust') ?>
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="<?= __('stock.search_placeholder') ?>"
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100"><?= __('common.search') ?></button>
            </div>
            <div class="col-md-2">
                <a href="<?= baseUrl('stock') ?>" class="btn btn-sm btn-outline-danger w-100"><?= __('common.clear') ?></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th><?= __('stock.product') ?></th><th><?= __('stock.sku') ?></th><th class="text-center"><?= __('stock.unit') ?></th><th class="text-end"><?= __('stock.quantity') ?></th><th class="text-center"><?= __('common.status') ?></th><th class="text-end"><?= __('common.actions') ?></th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-medium"><?= e($item->name) ?></td>
                        <td><?= e($item->sku ?? '-') ?></td>
                        <td class="text-center"><?= e($item->unit) ?></td>
                        <td class="text-end fw-bold <?= $item->quantity <= 5 ? 'text-danger' : ($item->quantity <= 10 ? 'text-warning' : '') ?>">
                            <?= $item->quantity ?>
                        </td>
                        <td class="text-center">
                            <?php if ($item->quantity <= 0): ?>
                                <span class="badge bg-danger"><?= __('stock.status_out') ?></span>
                            <?php elseif ($item->quantity <= 5): ?>
                                <span class="badge bg-warning"><?= __('stock.status_low') ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= __('stock.status_ok') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= baseUrl('stock/movements/' . $item->id) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
