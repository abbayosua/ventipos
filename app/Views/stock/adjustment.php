<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('stock.adjust_title') ?></h3>
    <a href="<?= baseUrl('stock') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl('stock/adjustment') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('stock.product') ?></label>
                    <select name="product_id" class="form-select" required>
                        <option value=""><?= __('common.select') ?>...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p->id ?>">
                                <?= e($p->name) ?> (SKU: <?= e($p->sku) ?>, <?= __('stock.current') ?>: <?= $p->current_qty ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('stock.type') ?></label>
                    <select name="type" class="form-select" required>
                        <option value="in"><?= __('stock.stock_in') ?></option>
                        <option value="out"><?= __('stock.stock_out') ?></option>
                        <option value="adjustment"><?= __('stock.set_qty') ?></option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('stock.quantity') ?></label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('stock.reason') ?></label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Damaged goods, returned to supplier..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= __('common.save') ?></button>
        </form>
    </div>
</div>
