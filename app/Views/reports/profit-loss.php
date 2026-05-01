<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Profit & Loss</h3>
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
    <div class="card-body">
        <table class="table table-borderless mb-0">
            <tr>
                <td class="fw-bold">Gross Sales</td>
                <td class="text-end"><?= formatMoney($salesData->gross_sales) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Less: Tax</td>
                <td class="text-end text-danger">-<?= formatMoney($salesData->tax) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Less: Discounts</td>
                <td class="text-end text-danger">-<?= formatMoney($salesData->discounts) ?></td>
            </tr>
            <tr class="border-top">
                <td class="fw-bold">Net Sales</td>
                <td class="text-end fw-bold"><?= formatMoney($netSales) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Less: Cost of Goods Sold (COGS)</td>
                <td class="text-end text-danger">-<?= formatMoney($cogs) ?></td>
            </tr>
            <tr class="border-top">
                <td class="fw-bold <?= $grossProfit >= 0 ? 'text-success' : 'text-danger' ?>">Gross Profit</td>
                <td class="text-end fw-bold <?= $grossProfit >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatMoney($grossProfit) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Less: Expenses</td>
                <td class="text-end text-danger">-<?= formatMoney($expenses) ?></td>
            </tr>
            <tr class="border-top border-dark">
                <td class="fw-bold fs-5 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">Net Profit / Loss</td>
                <td class="text-end fw-bold fs-5 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatMoney($netProfit) ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-4">
        <div class="card text-bg-primary"><div class="card-body"><h6>Transactions</h6><h4><?= $salesData->sale_count ?></h4></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning"><div class="card-body"><h6>Avg per Sale</h6><h4><?= $salesData->sale_count > 0 ? formatMoney($netSales / $salesData->sale_count) : formatMoney(0) ?></h4></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info"><div class="card-body"><h6>Margin</h6><h4><?= $netSales > 0 ? number_format(($grossProfit / $netSales) * 100, 1) . '%' : '0%' ?></h4></div></div>
    </div>
</div>
