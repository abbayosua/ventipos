<div class="d-flex gap-2 mb-3">
    <h3 class="mb-0"><?= __('settings.outlets') ?></h3>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings') ?>"><?= __('settings.company') ?></a></li>
    <li class="nav-item"><a class="nav-link active" href="<?= baseUrl('settings/outlets') ?>"><?= __('settings.outlets') ?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= baseUrl('settings/users') ?>"><?= __('settings.users') ?></a></li>
</ul>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><?= __('settings.new_outlet') ?></div>
            <div class="card-body">
                <form method="POST" action="<?= baseUrl('settings/outlets/store') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.name') ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.code') ?></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. STORE-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.phone') ?></label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.email') ?></label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('common.address') ?></label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= __('settings.create_outlet') ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th><?= __('common.name') ?></th><th><?= __('common.code') ?></th><th><?= __('common.phone') ?></th><th class="text-end"><?= __('common.actions') ?></th></tr></thead>
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
            <i class="bi bi-info-circle"></i> <?= __('settings.switch_hint') ?>
        </div>
    </div>
</div>
