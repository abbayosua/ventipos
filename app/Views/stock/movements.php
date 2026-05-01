<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Stock Movements: <?= e($product->name) ?></h3>
    <a href="<?= baseUrl('stock') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Date</th><th>Type</th><th class="text-end">Quantity</th><th>Reference</th><th>Notes</th><th>By</th></tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No movements recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): ?>
                    <tr>
                        <td><?= formatDate($m->created_at, 'd M H:i') ?></td>
                        <td>
                            <?php if ($m->type === 'in'): ?>
                                <span class="badge bg-success">In</span>
                            <?php elseif ($m->type === 'out'): ?>
                                <span class="badge bg-danger">Out</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Adjust</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold"><?= $m->quantity ?></td>
                        <td><?= $m->reference_type ? e(ucfirst($m->reference_type) . ' #' . $m->reference_id) : '-' ?></td>
                        <td class="text-muted"><?= e($m->notes ?? '-') ?></td>
                        <td><?= e($m->user_name ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
