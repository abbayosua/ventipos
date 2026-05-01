<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="text-center mb-4"><?= __('auth.register_title') ?></h4>
                <form method="POST" action="<?= baseUrl('register') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.name') ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.email') ?></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.password') ?></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.company_name') ?></label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><?= __('auth.create_account') ?></button>
                    <p class="text-center mt-3 mb-0">
                        <a href="<?= baseUrl('login') ?>"><?= __('auth.have_account') ?></a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
