<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0"><?= __('settings.users') ?></h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings') ?>"><?= __('settings.company') ?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/outlets') ?>"><?= __('settings.outlets') ?></a></li>
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings/users') ?>"><?= __('settings.users') ?></a></li>
</ul>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><?= __('settings.invite_user') ?></div>
            <div class="card-body">
                <form method="POST" action="<?= baseUrl('settings/users/invite') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.name') ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.email') ?></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.password') ?></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('settings.role') ?></label>
                        <select name="role" class="form-select">
                            <option value="cashier"><?= __('settings.cashier') ?></option>
                            <option value="admin"><?= __('settings.admin') ?></option>
                            <option value="owner"><?= __('settings.owner') ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= __('settings.invite') ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th><?= __('common.name') ?></th><th><?= __('common.email') ?></th><th><?= __('settings.role') ?></th><th><?= __('common.status') ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="fw-medium"><?= e($u->name) ?></td>
                            <td><?= e($u->email) ?></td>
                            <td><span class="badge bg-<?= $u->role === 'owner' ? 'danger' : ($u->role === 'admin' ? 'warning' : 'secondary') ?>"><?= __("settings.{$u->role}") ?></span></td>
                            <td><?= $u->is_active ? '<span class="badge bg-success">' . __('common.active') . '</span>' : '<span class="badge bg-secondary">' . __('common.inactive') . '</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
