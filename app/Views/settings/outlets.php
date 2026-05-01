<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0">Outlets</h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings') ?>">Company</a></li>
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings/outlets') ?>">Outlets</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/users') ?>">Users</a></li>
</ul>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">New Outlet</div>
            <div class="card-body">
                <form method="POST" action="<?= baseUrl('settings/outlets/store') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. STORE-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Outlet</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Code</th><th>Phone</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($outlets as $o): ?>
                        <tr>
                            <td class="fw-medium"><?= e($o->name) ?></td>
                            <td><?= e($o->code) ?></td>
                            <td><?= e($o->phone ?? '-') ?></td>
                            <td class="text-end">
                                <form method="POST" action="<?= baseUrl('settings/switch-outlet') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="outlet_id" value="<?= $o->id ?>">
                                    <button class="btn btn-sm btn-outline-primary" title="Switch to this outlet">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2 text-muted small">
            <i class="bi bi-info-circle"></i> Click the arrow to switch the active outlet.
        </div>
    </div>
</div>
