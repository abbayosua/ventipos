<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('categories.title') ?></h3>
    <a href="<?= baseUrl('categories/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> <?= __('categories.create') ?>
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><?= __('categories.name') ?></th>
                    <th><?= __('common.description') ?></th>
                    <th class="text-center"><?= __('categories.product_count') ?></th>
                    <th class="text-end"><?= __('common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><?= __('common.no_data') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="fw-medium"><?= e($cat->name) ?></td>
                        <td class="text-muted"><?= e($cat->description ?? '-') ?></td>
                        <td class="text-center"><?= $cat->product_count ?></td>
                        <td class="text-end">
                            <a href="<?= baseUrl('categories/edit/' . $cat->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= baseUrl('categories/delete/' . $cat->id) ?>" class="d-inline"
                                  onsubmit="return confirm('<?= __('categories.delete_warning') ?>');">
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
