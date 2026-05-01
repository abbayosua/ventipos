<h3 class="mb-4">Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5>Today's Sales</h5>
                <h3><?= formatMoney($todaySales ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5>Transactions</h5>
                <h3><?= $todayCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5>Products</h5>
                <h3><?= $productCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <h5>Customers</h5>
                <h3><?= $customerCount ?? 0 ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Low Stock Alerts</div>
            <div class="card-body">
                <?php if (empty($lowStock)): ?>
                    <p class="text-muted mb-0">No low stock items.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Qty</th></tr></thead>
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
            <div class="card-header">Today's Sales</div>
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
                    label: 'Sales',
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
