<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="text-center mb-4">Login to <?= e(config('app.name')) ?></h4>
                <form method="POST" action="<?= baseUrl('login') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="text-center mt-3 mb-0">
                        <a href="<?= baseUrl('register') ?>">Create Account</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
