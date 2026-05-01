<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $customer ? __('customers.edit') : __('customers.create') ?></h3>
    <a href="<?= baseUrl('customers') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl($customer ? 'customers/update/' . $customer->id : 'customers/store') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('customers.name') ?></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($customer->name ?? old('name')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('common.email') ?></label>
                    <input type="email" name="email" class="form-control" value="<?= e($customer->email ?? old('email')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('common.phone') ?></label>
                    <input type="text" name="phone" class="form-control" value="<?= e($customer->phone ?? old('phone')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('customers.tax_number') ?></label>
                    <input type="text" name="tax_number" class="form-control" value="<?= e($customer->tax_number ?? old('tax_number')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('common.address') ?></label>
                <textarea name="address" class="form-control" rows="2"><?= e($customer->address ?? old('address')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $customer ? __('common.update') : __('common.create') ?></button>
        </form>
    </div>
</div>
