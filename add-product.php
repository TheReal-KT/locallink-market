<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_admin();
$categories = market_get_categories();
$sellers = market_get_seller_list();
$errors = [];
$form = [
    'title' => '',
    'seller_id' => '0',
    'category_id' => (string) ($categories[0]['id'] ?? 0),
    'price' => '',
    'description' => '',
    'stock' => '1',
    'status' => 'active',
];

if (app_is_post_request()) {
    $form['title'] = trim((string) ($_POST['title'] ?? ''));
    $form['seller_id'] = (string) max(0, (int) ($_POST['seller_id'] ?? 0));
    $form['category_id'] = (string) max(0, (int) ($_POST['category_id'] ?? 0));
    $form['price'] = trim((string) ($_POST['price'] ?? ''));
    $form['description'] = trim((string) ($_POST['description'] ?? ''));
    $form['stock'] = trim((string) ($_POST['stock'] ?? '1'));
    $form['status'] = strtolower(trim((string) ($_POST['status'] ?? 'active')));

    if ($form['title'] === '') {
        $errors[] = 'Enter a product title.';
    }

    if ((int) $form['category_id'] < 1) {
        $errors[] = 'Choose a category.';
    }

    if (!is_numeric($form['price']) || (float) $form['price'] <= 0) {
        $errors[] = 'Enter a valid price.';
    }

    if ($form['description'] === '') {
        $errors[] = 'Add a short product description.';
    }

    if (!ctype_digit($form['stock']) || (int) $form['stock'] < 1) {
        $errors[] = 'Stock must be at least 1.';
    }

    if (!in_array($form['status'], ['active', 'inactive', 'pending_review', 'archived'], true)) {
        $errors[] = 'Choose a valid listing status.';
    }

    if ($errors === []) {
        try {
            market_create_product([
                'title' => $form['title'],
                'seller_id' => (int) $form['seller_id'],
                'category_id' => (int) $form['category_id'],
                'price' => (float) $form['price'],
                'description' => $form['description'],
                'stock' => (int) $form['stock'],
                'status' => $form['status'],
            ]);
            app_set_flash('success', 'Product saved successfully.');
            app_redirect('admin/dashboard.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Add Product';
$pageDescription = 'Create a product and assign it to a seller if needed.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card stack">
    <p class="eyebrow">Admin product management</p>
    <h1>Add a new product</h1>
    <p>Create a catalog item directly or attach the item to an approved seller listing for moderation.</p>
    <div class="feature-list">
      <div>Choose a category and seller</div>
      <div>Set the price, stock, and moderation status</div>
      <div>Use the seller workflows for later product edits</div>
    </div>
  </div>
  <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">
    <p class="eyebrow">Product form</p>
    <?php if ($errors !== []): ?>
      <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="field">
      <label for="product-title">Product title</label>
      <input id="product-title" name="title" type="text" placeholder="Product name" value="<?php echo htmlspecialchars($form['title']); ?>">
    </div>
    <div class="field">
      <label for="seller">Assign seller</label>
      <select id="seller" name="seller_id">
        <option value="0" <?php echo $form['seller_id'] === '0' ? 'selected' : ''; ?>>Store-managed listing</option>
        <?php foreach ($sellers as $seller): ?>
          <option value="<?php echo (int) $seller['id']; ?>" <?php echo (int) $form['seller_id'] === (int) $seller['id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($seller['name'] . ' (' . $seller['role_label'] . ')'); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="category">Category</label>
      <select id="category" name="category_id">
        <?php foreach ($categories as $category): ?>
          <option value="<?php echo (int) $category['id']; ?>" <?php echo (int) $form['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($category['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field-row">
      <div class="field">
        <label for="price">Price</label>
        <input id="price" name="price" type="number" min="1" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($form['price']); ?>">
      </div>
      <div class="field">
        <label for="stock">Stock</label>
        <input id="stock" name="stock" type="number" min="1" step="1" value="<?php echo htmlspecialchars($form['stock']); ?>">
      </div>
    </div>
    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'pending_review' => 'Pending review', 'archived' => 'Archived'] as $value => $label): ?>
          <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $form['status'] === $value ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="5" placeholder="Short product description"><?php echo htmlspecialchars($form['description']); ?></textarea>
    </div>
    <button class="button" type="submit">Save product</button>
    <p class="hint">Each product uses a default category image to keep the project small and consistent.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
