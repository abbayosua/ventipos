<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= __('reports.daily') ?></h3>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-sm" value="<?= e($date) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100"><?= __('reports.view') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary"><div class="card-body"><h6><?= __('reports.sales_count') ?></h6><h4><?= $summary->count ?></h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success"><div class="card-body"><h6><?= __('reports.total_sales') ?></h6><h4><?= formatMoney($summary->total) ?></h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info"><div class="card-body"><h6><?= __('reports.tax_collected') ?></h6><h4><?= formatMoney($summary->tax) ?></h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger"><div class="card-body"><h6><?= __('reports.expenses') ?></h6><h4><?= formatMoney($expenseTotal) ?></h4></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><?= __('sales.title') ?></span>
        <a href="<?= baseUrl('reports/daily?date=' . $date) ?>" class="btn btn-sm btn-outline-secondary"><?= __('reports.refresh') ?></a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th><?= __('sales.invoice') ?></th><th><?= __('sales.customer') ?></th><th class="text-end"><?= __('common.total') ?></th><th><?= __('common.paid') ?></th><th><?= __('common.time') ?></th></tr></thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><?= __('reports.no_sales') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($sales as $s): ?>
                    <tr>
                        <td><a href="<?= baseUrl('sales/' . $s->id) ?>"><?= e($s->invoice_no) ?></a></td>
                        <td><?= e($s->customer_name ?? '-') ?></td>
                        <td class="text-end fw-bold"><?= formatMoney($s->total) ?></td>
                        <td><span class="badge bg-<?= $s->payment_status === 'paid' ? 'success' : 'warning' ?>"><?= e($s->payment_method) ?></span></td>
                        <td><?= formatDate($s->created_at, 'H:i') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
