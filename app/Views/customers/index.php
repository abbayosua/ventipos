<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('customers.title') ?></h3>
    <a href="<?= baseUrl('customers/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> <?= __('customers.create') ?></a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="<?= __('customers.search_placeholder') ?>"
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100"><?= __('common.search') ?></button>
            </div>
            <div class="col-md-2">
                <a href="<?= baseUrl('customers') ?>" class="btn btn-sm btn-outline-danger w-100"><?= __('common.clear') ?></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th><?= __('common.name') ?></th><th><?= __('common.phone') ?></th><th><?= __('common.email') ?></th><th><?= __('customers.tax_number') ?></th><th class="text-end"><?= __('common.actions') ?></th></tr></thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td class="fw-medium"><?= e($c->name) ?></td>
                        <td><?= e($c->phone ?? '-') ?></td>
                        <td><?= e($c->email ?? '-') ?></td>
                        <td><?= e($c->tax_number ?? '-') ?></td>
                        <td class="text-end">
                            <a href="<?= baseUrl('customers/edit/' . $c->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= baseUrl('customers/delete/' . $c->id) ?>" class="d-inline" onsubmit="return confirm('<?= __('common.confirm') ?>');">
                                <?= csrfField() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
