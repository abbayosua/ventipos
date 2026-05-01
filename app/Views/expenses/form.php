<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('expenses.create') ?></h3>
    <a href="<?= baseUrl('expenses') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> <?= __('common.back') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl('expenses/store') ?>">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('expenses.category') ?></label>
                    <select name="category" class="form-select" required>
                        <option value="Utilities"><?= __('expenses.utilities') ?></option>
                        <option value="Rent"><?= __('expenses.rent') ?></option>
                        <option value="Supplies"><?= __('expenses.supplies') ?></option>
                        <option value="Maintenance"><?= __('expenses.maintenance') ?></option>
                        <option value="Salary"><?= __('expenses.salary') ?></option>
                        <option value="Marketing"><?= __('expenses.marketing') ?></option>
                        <option value="Transport"><?= __('expenses.transport') ?></option>
                        <option value="Food"><?= __('expenses.food') ?></option>
                        <option value="Other"><?= __('pos.other') ?></option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('expenses.amount') ?></label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= __('expenses.date') ?></label>
                    <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('expenses.description') ?></label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= __('common.save') ?></button>
        </form>
    </div>
</div>
