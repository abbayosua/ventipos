<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Products</h3>
    <a href="<?= baseUrl('products/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Product
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, SKU or barcode..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm" data-auto-submit>
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= ($selectedCategory ?? '') == $cat->id ? 'selected' : '' ?>>
                            <?= e($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= baseUrl('products') ?>" class="btn btn-sm btn-outline-danger w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Price</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="fw-medium"><?= e($p->name) ?></td>
                        <td class="text-muted"><?= e($p->sku ?? '-') ?></td>
                        <td><?= e($p->category_name ?? '-') ?></td>
                        <td class="text-end"><?= formatMoney($p->cost_price) ?></td>
                        <td class="text-end"><?= formatMoney($p->selling_price) ?></td>
                        <td class="text-center">
                            <a href="<?= baseUrl('products/edit/' . $p->id) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= baseUrl('products/delete/' . $p->id) ?>" class="d-inline"
                                  onsubmit="return confirm('Delete this product?');">
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
