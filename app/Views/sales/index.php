<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Sales History</h3>
    <a href="<?= baseUrl('pos') ?>" class="btn btn-primary btn-sm"><i class="bi bi-cart"></i> New Sale</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($sales as $s): ?>
                    <tr>
                        <td class="fw-medium"><?= e($s->invoice_no) ?></td>
                        <td><?= e($s->customer_name ?? 'Walk-in') ?></td>
                        <td><?= \App\Core\Database::fetch("SELECT COUNT(*) as c FROM sale_items WHERE sale_id = ?", [$s->id])->c ?></td>
                        <td class="text-end"><?= formatMoney($s->total) ?></td>
                        <td class="text-center">
                            <?php if ($s->payment_status === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($s->payment_status === 'partial'): ?>
                                <span class="badge bg-warning">Partial</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDate($s->created_at, 'd M H:i') ?></td>
                        <td class="text-end">
                            <a href="<?= baseUrl('sales/' . $s->id) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
