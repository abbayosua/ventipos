<h3 class="mb-4"><?= __('dashboard.title') ?></h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5><?= __('dashboard.todays_sales') ?></h5>
                <h3><?= formatMoney($todaySales ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5><?= __('dashboard.transactions') ?></h5>
                <h3><?= $todayCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5><?= __('dashboard.products') ?></h5>
                <h3><?= $productCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <h5><?= __('dashboard.customers') ?></h5>
                <h3><?= $customerCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><?= __('dashboard.low_stock') ?></div>
            <div class="card-body">
                <?php if (empty($lowStock)): ?>
                    <p class="text-muted mb-0"><?= __('dashboard.no_low_stock') ?></p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead><tr><th><?= __('common.name') ?></th><th><?= __('common.qty') ?></th></tr></thead>
                        <tbody>
                            <?php foreach ($lowStock as $item): ?>
                                <tr>
                                    <td><?= e($item->product_name) ?></td>
                                    <td class="text-danger"><?= $item->quantity ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><?= __('dashboard.hourly_sales') ?></div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                datasets: [{
                    label: '<?= __('dashboard.todays_sales') ?>',
                    data: <?= json_encode($chartData ?? []) ?>,
                    borderColor: '#0d6efd',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
