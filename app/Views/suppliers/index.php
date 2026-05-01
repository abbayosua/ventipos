<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Suppliers</h3>
    <a href="<?= baseUrl('suppliers/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Supplier</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone or email..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= baseUrl('suppliers') ?>" class="btn btn-sm btn-outline-danger w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No suppliers yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td class="fw-medium"><?= e($s->name) ?></td>
                        <td><?= e($s->contact_person ?? '-') ?></td>
                        <td><?= e($s->phone ?? '-') ?></td>
                        <td><?= e($s->email ?? '-') ?></td>
                        <td class="text-end">
                            <a href="<?= baseUrl('suppliers/edit/' . $s->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= baseUrl('suppliers/delete/' . $s->id) ?>" class="d-inline" onsubmit="return confirm('Delete?');">
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
