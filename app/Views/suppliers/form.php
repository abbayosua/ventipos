<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $supplier ? 'Edit' : 'New' ?> Supplier</h3>
    <a href="<?= baseUrl('suppliers') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl($supplier ? 'suppliers/update/' . $supplier->id : 'suppliers/store') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($supplier->name ?? old('name')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= e($supplier->contact_person ?? old('contact_person')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($supplier->phone ?? old('phone')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($supplier->email ?? old('email')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tax Number</label>
                    <input type="text" name="tax_number" class="form-control" value="<?= e($supplier->tax_number ?? old('tax_number')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?= e($supplier->address ?? old('address')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $supplier ? 'Update' : 'Create' ?></button>
        </form>
    </div>
</div>
