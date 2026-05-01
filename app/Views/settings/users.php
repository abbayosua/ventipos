<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0">Users</h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings') ?>">Company</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/outlets') ?>">Outlets</a></li>
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings/users') ?>">Users</a></li>
</ul>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Invite User</div>
            <div class="card-body">
                <form method="POST" action="<?= baseUrl('settings/users/invite') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="cashier">Cashier</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Invite</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="fw-medium"><?= e($u->name) ?></td>
                            <td><?= e($u->email) ?></td>
                            <td><span class="badge bg-<?= $u->role === 'owner' ? 'danger' : ($u->role === 'admin' ? 'warning' : 'secondary') ?>"><?= e($u->role) ?></span></td>
                            <td><?= $u->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
