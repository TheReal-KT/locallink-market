<?php
require __DIR__ . '/includes/seller_tools.php';

$sellerUser = seller_require_account();
$categories = market_get_categories();
$statusOptions = seller_product_status_options();
$productId = isset($_GET['id']) ? max(0, (int) $_GET['id']) : 0;
$product = $productId > 0 ? seller_find_product_for_user($sellerUser, $productId) : null;
$errors = [];
$capabilities = seller_capabilities();

if ($productId > 0 && $product === null) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    $pageDescription = 'The seller listing you requested could not be found.';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page section">
      <div class="card stack">
        <p class="eyebrow">Listing unavailable</p>
        <h1>This seller listing could not be found.</h1>
        <p>The product may not belong to this seller account or it may have been removed.</p>
        <a class="button" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">Back to listings</a>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    return;
}

$form = [
    'title' => (string) ($product['title'] ?? ''),
    'category_id' => (int) ($product['category_id'] ?? ($categories[0]['id'] ?? 0)),
    'price' => isset($product['price_amount']) ? (string) $product['price_amount'] : '',
    'stock' => isset($product['stock']) ? (string) $product['stock'] : '1',
    'location' => (string) ($product['location'] ?? ''),
    'status' => (string) ($product['raw_status'] ?? $statusOptions[0]),
    'description' => (string) ($product['description'] ?? ''),
];

if (app_is_post_request()) {
    $form['title'] = trim((string) ($_POST['title'] ?? ''));
    $form['category_id'] = max(0, (int) ($_POST['category_id'] ?? 0));
    $form['price'] = trim((string) ($_POST['price'] ?? ''));
    $form['stock'] = trim((string) ($_POST['stock'] ?? '1'));
    $form['location'] = trim((string) ($_POST['location'] ?? ''));
    $form['status'] = strtolower(trim((string) ($_POST['status'] ?? $statusOptions[0])));
    $form['description'] = trim((string) ($_POST['description'] ?? ''));

    try {
        $savedId = seller_save_product($sellerUser, $form, $productId > 0 ? $productId : null);
        app_set_flash('success', $productId > 0 ? 'Listing updated successfully.' : 'Listing created successfully.');
        app_redirect('edit-product.php?id=' . $savedId);
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$pageTitle = $productId > 0 ? 'Edit Seller Product' : 'Add Seller Product';
$pageDescription = 'Create or update a seller-owned listing.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Seller workspace</strong>
    <?php foreach (seller_navigation_items() as $item): ?>
      <a class="<?php echo $item['key'] === 'products' ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['path'])); ?>">
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Seller product form</p>
        <h1><?php echo $productId > 0 ? 'Update your listing' : 'Create a new listing'; ?></h1>
      </div>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">Back to listings</a>
    </div>
    <?php if (!$capabilities['product_owner_support']): ?>
      <div class="notice">
        This form is ready for the seller flow, but saving depends on shared support for `products.seller_id` and seller-owned product queries.
      </div>
    <?php endif; ?>
    <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('edit-product.php' . ($productId > 0 ? '?id=' . $productId : ''))); ?>">
      <p class="eyebrow"><?php echo $productId > 0 ? 'Edit listing' : 'New listing'; ?></p>
      <?php if ($errors !== []): ?>
        <div class="notice notice-error">
          <?php foreach ($errors as $error): ?>
            <div><?php echo htmlspecialchars($error); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="field">
        <label for="title">Product title</label>
        <input id="title" name="title" type="text" placeholder="Product name" value="<?php echo htmlspecialchars($form['title']); ?>">
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
      <div class="field-row">
        <div class="field">
          <label for="location">Location</label>
          <input id="location" name="location" type="text" placeholder="Johannesburg pickup or delivery area" value="<?php echo htmlspecialchars($form['location']); ?>">
        </div>
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statusOptions as $status): ?>
              <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $form['status'] === $status ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(seller_humanize_product_status($status)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6" placeholder="Short product description"><?php echo htmlspecialchars($form['description']); ?></textarea>
      </div>
      <div class="form-actions">
        <button class="button" type="submit"><?php echo $productId > 0 ? 'Save changes' : 'Create listing'; ?></button>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">Cancel</a>
      </div>
      <p class="hint">The listing uses the default category image so the seller flow stays lightweight and consistent with the rest of the project.</p>
    </form>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
