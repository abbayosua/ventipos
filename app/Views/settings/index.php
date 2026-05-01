<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0">Settings</h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings') ?>">Company</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/outlets') ?>">Outlets</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/users') ?>">Users</a></li>
</ul>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl('settings/update') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($company->name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($company->email) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($company->phone) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Currency Code</label>
                    <input type="text" name="currency_code" class="form-control" value="<?= e($company->currency_code) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control" value="<?= e($company->currency_symbol) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?= e($company->address) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Currency Rates</div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <thead><tr><th>Code</th><th>Symbol</th><th>Rate (vs base)</th><th>Base</th></tr></thead>
            <tbody>
                <?php
                $rates = \App\Core\Database::fetchAll(
                    "SELECT * FROM currency_rates WHERE company_id = ? ORDER BY is_base DESC",
                    [$company->id]
                );
                ?>
                <?php foreach ($rates as $r): ?>
                <tr>
                    <td><?= e($r->code) ?></td>
                    <td><?= e($r->symbol) ?></td>
                    <td><?= $r->rate ?></td>
                    <td><?= $r->is_base ? '✓' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
