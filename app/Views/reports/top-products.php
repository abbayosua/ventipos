<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Top Selling Products</h3>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="small mb-0">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="small mb-0">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Product</th><th>SKU</th><th class="text-end">Qty Sold</th><th class="text-end">Revenue</th><th class="text-end">Profit</th></tr></thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="fw-medium"><?= e($p->name) ?></td>
                    <td><?= e($p->sku) ?></td>
                    <td class="text-end"><?= $p->total_qty ?></td>
                    <td class="text-end"><?= formatMoney($p->total_revenue) ?></td>
                    <td class="text-end <?= $p->total_profit >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatMoney($p->total_profit) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No data for this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
