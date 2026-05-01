<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body p-4">
                <!-- Step Indicator -->
                <div class="d-flex mb-4 gap-1 text-center small">
                    <div class="flex-fill py-2 rounded <?= $step >= 1 ? 'bg-primary text-white' : 'bg-light' ?>">1. Account</div>
                    <div class="flex-fill py-2 rounded <?= $step >= 2 ? 'bg-primary text-white' : 'bg-light' ?>">2. Store</div>
                    <div class="flex-fill py-2 rounded <?= $step >= 3 ? 'bg-primary text-white' : 'bg-light' ?>">3. Data</div>
                    <div class="flex-fill py-2 rounded <?= $step >= 4 ? 'bg-primary text-white' : 'bg-light' ?>">4. Done</div>
                </div>

                <!-- Step 1: Account -->
                <?php if ($step === 1): ?>
                <form method="POST" action="<?= baseUrl('register') ?>">
                    <input type="hidden" name="action" value="step1">
                    <h4 class="mb-3"><?= __('auth.register_title') ?></h4>
                    <p class="text-muted mb-3">Create your account to get started.</p>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.name') ?></label>
                        <input type="text" name="name" class="form-control form-control-lg"
                               value="<?= e($wizard['name'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.email') ?></label>
                        <input type="email" name="email" class="form-control form-control-lg"
                               value="<?= e($wizard['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('auth.password') ?></label>
                        <input type="password" name="password" class="form-control form-control-lg"
                               minlength="6" required>
                        <div class="form-text"><?= __('install.min_chars') ?></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Continue →</button>
                    <p class="text-center mt-3 mb-0">
                        <a href="<?= baseUrl('login') ?>"><?= __('auth.have_account') ?></a>
                    </p>
                </form>
                <?php endif; ?>

                <!-- Step 2: Store Setup -->
                <?php if ($step === 2): ?>
                <form method="POST" action="<?= baseUrl('register') ?>">
                    <input type="hidden" name="action" value="step2">
                    <h4 class="mb-3">🏪 Store Setup</h4>
                    <p class="text-muted mb-3">Tell us about your business and choose your currency.</p>
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-lg"
                               value="<?= e($wizard['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outlet Name</label>
                        <input type="text" name="outlet_name" class="form-control form-control-lg"
                               value="<?= e($wizard['outlet_name'] ?? 'Main Store') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Currency</label>
                        <select name="base_currency" class="form-select form-select-lg" required>
                            <?php
                            $currencies = [
                                'IDR' => ['Rp', 'Indonesian Rupiah'],
                                'USD' => ['$', 'US Dollar'],
                                'EUR' => ['€', 'Euro'],
                                'GBP' => ['£', 'British Pound'],
                                'SGD' => ['S$', 'Singapore Dollar'],
                                'MYR' => ['RM', 'Malaysian Ringgit'],
                                'PHP' => ['₱', 'Philippine Peso'],
                                'THB' => ['฿', 'Thai Baht'],
                                'VND' => ['₫', 'Vietnamese Dong'],
                                'CNY' => ['¥', 'Chinese Yuan'],
                                'AUD' => ['A$', 'Australian Dollar'],
                                'JPY' => ['¥', 'Japanese Yen'],
                                'KRW' => ['₩', 'South Korean Won'],
                                'INR' => ['₹', 'Indian Rupee'],
                            ];
                            $selected = $wizard['base_currency'] ?? 'IDR';
                            ?>
                            <?php foreach ($currencies as $code => $meta): ?>
                                <option value="<?= $code ?>" <?= $code === $selected ? 'selected' : '' ?>>
                                    <?= $code ?> — <?= $meta[0] ?> (<?= $meta[1] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">This is your base currency. All product prices and sales will be stored in this currency. It cannot be changed later.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($wizard['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address (optional)</label>
                            <input type="text" name="address" class="form-control" value="<?= e($wizard['address'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= baseUrl('register') ?>?step=1" class="btn btn-outline-secondary btn-lg flex-grow-1">← Back</a>
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">Continue →</button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- Step 3: Demo Data -->
                <?php if ($step === 3): ?>
                <form method="POST" action="<?= baseUrl('register') ?>">
                    <input type="hidden" name="action" value="step3">
                    <h4 class="mb-3">🌱 Demo Data</h4>
                    <p class="text-muted mb-3">Would you like to start with sample data?</p>
                    <div class="border rounded p-4 mb-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="seed_demo" value="1" id="seedYes" checked>
                            <label class="form-check-label fw-bold" for="seedYes">
                                ✅ Yes, seed demo data
                            </label>
                            <div class="text-muted small mt-1">Add 18 sample products, 5 customers, 3 suppliers, 8 categories, and EUR currency rate so you can start using the system immediately.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="seed_demo" value="0" id="seedNo">
                            <label class="form-check-label fw-bold" for="seedNo">
                                ❌ No, start empty
                            </label>
                            <div class="text-muted small mt-1">Start with a blank catalog. You'll add products and customers manually.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= baseUrl('register') ?>?step=2" class="btn btn-outline-secondary btn-lg flex-grow-1">← Back</a>
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">Continue →</button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- Step 4: Complete -->
                <?php if ($step === 4): ?>
                <form method="POST" action="<?= baseUrl('register') ?>">
                    <input type="hidden" name="action" value="complete">
                    <h4 class="mb-3">🎉 Almost Done!</h4>
                    <div class="border rounded p-3 mb-3 bg-light">
                        <strong>Account:</strong> <?= e($wizard['name'] ?? '-') ?> (<?= e($wizard['email'] ?? '-') ?>)<br>
                        <strong>Company:</strong> <?= e($wizard['company_name'] ?? '-') ?><br>
                        <strong>Outlet:</strong> <?= e($wizard['outlet_name'] ?? '-') ?><br>
                        <strong>Base Currency:</strong> <?= e($wizard['base_currency'] ?? 'IDR') ?><br>
                        <strong>Demo Data:</strong> <?= !empty($wizard['seed_demo']) ? '✅ Yes' : '❌ No' ?>
                    </div>
                    <p class="text-muted mb-3">Click "Create Account" to complete your registration. You'll be redirected to login.</p>
                    <button type="submit" class="btn btn-success btn-lg w-100">🚀 Create Account</button>
                    <p class="text-center mt-3 mb-0">
                        <a href="<?= baseUrl('register') ?>?step=3" class="text-muted">← Back</a>
                    </p>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
