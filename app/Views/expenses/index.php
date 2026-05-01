<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('expenses.title') ?></h3>
    <a href="<?= baseUrl('expenses/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> <?= __('expenses.create') ?></a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label small mb-0"><?= __('reports.from') ?></label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0"><?= __('reports.to') ?></label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100"><?= __('common.filter') ?></button>
            </div>
            <div class="col-md-4 d-flex align-items-end justify-content-end">
                <strong><?= __('expenses.total') ?>: <?= formatMoney($total) ?></strong>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th><?= __('common.date') ?></th><th><?= __('expenses.category') ?></th><th><?= __('common.description') ?></th><th class="text-end"><?= __('expenses.amount') ?></th><th><?= __('expenses.by') ?></th><th class="text-end"><?= __('common.actions') ?></th></tr></thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                    <tr>
                        <td><?= formatDate($e->expense_date) ?></td>
                        <td><span class="badge bg-secondary"><?= e($e->category) ?></span></td>
                        <td class="text-muted"><?= e($e->description ?? '-') ?></td>
                        <td class="text-end fw-bold text-danger"><?= formatMoney($e->amount) ?></td>
                        <td><?= e($e->user_name ?? '-') ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?= baseUrl('expenses/delete/' . $e->id) ?>" class="d-inline" onsubmit="return confirm('<?= __('common.confirm') ?>');">
                                <?= csrfField() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
