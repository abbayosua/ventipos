<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Inventory Stock</h3>
    <a href="<?= baseUrl('stock/adjustment') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Adjust Stock
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search product..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= baseUrl('stock') ?>" class="btn btn-sm btn-outline-danger w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Product</th><th>SKU</th><th class="text-center">Unit</th><th class="text-end">Quantity</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr>
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
                                <span class="badge bg-danger">Out</span>
                            <?php elseif ($item->quantity <= 5): ?>
                                <span class="badge bg-warning">Low</span>
                            <?php else: ?>
                                <span class="badge bg-success">OK</span>
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
