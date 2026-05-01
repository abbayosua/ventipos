<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $supplier ? __('suppliers.edit') : __('suppliers.create') ?></h3>
    <a href="<?= baseUrl('suppliers') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl($supplier ? 'suppliers/update/' . $supplier->id : 'suppliers/store') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('suppliers.name') ?></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($supplier->name ?? old('name')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('suppliers.contact_person') ?></label>
                    <input type="text" name="contact_person" class="form-control" value="<?= e($supplier->contact_person ?? old('contact_person')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('common.phone') ?></label>
                    <input type="text" name="phone" class="form-control" value="<?= e($supplier->phone ?? old('phone')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('common.email') ?></label>
                    <input type="email" name="email" class="form-control" value="<?= e($supplier->email ?? old('email')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('suppliers.tax_number') ?></label>
                    <input type="text" name="tax_number" class="form-control" value="<?= e($supplier->tax_number ?? old('tax_number')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('common.address') ?></label>
                <textarea name="address" class="form-control" rows="2"><?= e($supplier->address ?? old('address')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $supplier ? __('common.update') : __('common.create') ?></button>
        </form>
    </div>
</div>
