<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $product ? __('products.edit') : __('products.create') ?></h3>
    <a href="<?= baseUrl('products') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl($product ? 'products/update/' . $product->id : 'products/store') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('products.name') ?></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($product->name ?? old('name')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('products.sku') ?></label>
                    <input type="text" name="sku" class="form-control" value="<?= e($product->sku ?? old('sku')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('products.barcode') ?></label>
                    <input type="text" name="barcode" class="form-control" value="<?= e($product->barcode ?? old('barcode')) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('products.category') ?></label>
                    <select name="category_id" class="form-select">
                        <option value=""><?= __('products.no_category') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (($product->category_id ?? old('category_id')) == $cat->id) ? 'selected' : '' ?>>
                                <?= e($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label"><?= __('products.unit') ?></label>
                    <input type="text" name="unit" class="form-control" value="<?= e($product->unit ?? old('unit', 'pcs')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('products.cost_price') ?></label>
                    <input type="number" step="0.01" min="0" name="cost_price" class="form-control" value="<?= e($product->cost_price ?? old('cost_price', 0)) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('products.selling_price') ?></label>
                    <input type="number" step="0.01" min="0" name="selling_price" class="form-control" value="<?= e($product->selling_price ?? old('selling_price', 0)) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label"><?= __('products.tax_rate') ?></label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" class="form-control" value="<?= e($product->tax_rate ?? old('tax_rate', 0)) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= __('common.description') ?></label>
                <textarea name="description" class="form-control" rows="2"><?= e($product->description ?? old('description')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><?= $product ? __('common.update') : __('common.create') ?></button>
        </form>
    </div>
</div>
