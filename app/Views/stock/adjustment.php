<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Stock Adjustment</h3>
    <a href="<?= baseUrl('stock') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl('stock/adjustment') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select product...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p->id ?>">
                                <?= e($p->name) ?> (SKU: <?= e($p->sku) ?>, Current: <?= $p->current_qty ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="in">Stock In (Add)</option>
                        <option value="out">Stock Out (Remove)</option>
                        <option value="adjustment">Set Exact Quantity</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes / Reason</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Damaged goods, returned to supplier..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Adjustment</button>
        </form>
    </div>
</div>
