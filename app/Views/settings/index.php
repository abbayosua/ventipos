<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0"><?= __('settings.title') ?></h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings') ?>"><?= __('settings.company') ?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/outlets') ?>"><?= __('settings.outlets') ?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/users') ?>"><?= __('settings.users') ?></a></li>
</ul>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl('settings/update') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('settings.company_name') ?></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($company->name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('common.email') ?></label>
                    <input type="email" name="email" class="form-control" value="<?= e($company->email) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('common.phone') ?></label>
                    <input type="text" name="phone" class="form-control" value="<?= e($company->phone) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= __('settings.currency_code') ?></label>
                    <select name="currency_code" class="form-select" onchange="updateCurrencySymbol(this)">
                        <?php
                        $currencies = [
                            'USD' => ['$', 'US Dollar'],
                            'IDR' => ['Rp', 'Indonesian Rupiah'],
                            'EUR' => ['€', 'Euro'],
                            'GBP' => ['£', 'British Pound'],
                            'JPY' => ['¥', 'Japanese Yen'],
                            'SGD' => ['S$', 'Singapore Dollar'],
                            'MYR' => ['RM', 'Malaysian Ringgit'],
                            'PHP' => ['₱', 'Philippine Peso'],
                            'THB' => ['฿', 'Thai Baht'],
                            'VND' => ['₫', 'Vietnamese Dong'],
                            'CNY' => ['¥', 'Chinese Yuan'],
                            'AUD' => ['A$', 'Australian Dollar'],
                            'CAD' => ['C$', 'Canadian Dollar'],
                            'CHF' => ['Fr', 'Swiss Franc'],
                            'KRW' => ['₩', 'South Korean Won'],
                            'INR' => ['₹', 'Indian Rupee'],
                            'SAR' => ['﷼', 'Saudi Riyal'],
                            'AED' => ['د.إ', 'UAE Dirham'],
                        ];
                        ?>
                        <?php foreach ($currencies as $code => $meta): ?>
                            <option value="<?= $code ?>" data-symbol="<?= $meta[0] ?>"
                                <?= $company->currency_code === $code ? 'selected' : '' ?>>
                                <?= $code ?> — <?= $meta[0] ?> (<?= $meta[1] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="currency_symbol" id="currencySymbol" value="<?= e($company->currency_symbol) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('common.address') ?></label>
                <textarea name="address" class="form-control" rows="2"><?= e($company->address) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= __('settings.save') ?></button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= __('settings.currency_rates') ?></span>
        <form method="POST" action="<?= baseUrl('settings/fetch-rates') ?>" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-cloud-download"></i> Fetch Live Rates
            </button>
        </form>
    </div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <thead><tr><th><?= __('settings.currency_code') ?></th><th><?= __('settings.currency_symbol') ?></th><th><?= __('common.amount') ?> (vs base)</th><th><?= __('common.status') ?></th></tr></thead>
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
                    <td><?= $r->is_base ? '✓ Base' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateCurrencySymbol(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('currencySymbol').value = opt.dataset.symbol;
}
</script>
