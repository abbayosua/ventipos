<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= $category ? 'Edit' : 'New' ?> Category</h3>
    <a href="<?= baseUrl('categories') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= baseUrl($category ? 'categories/update/' . $category->id : 'categories/store') ?>">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required
                       value="<?= e($category->name ?? old('name')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($category->description ?? old('description')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <?= $category ? 'Update' : 'Create' ?>
            </button>
        </form>
    </div>
</div>
