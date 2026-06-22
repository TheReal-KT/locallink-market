<?php
require __DIR__ . '/includes/seller_tools.php';

$sellerUser = seller_require_account();
$search = trim((string) ($_GET['search'] ?? ''));
$selectedStatus = strtolower(trim((string) ($_GET['status'] ?? '')));
$products = seller_get_products_for_user($sellerUser, [
    'search' => $search,
    'status' => $selectedStatus,
]);
$statusOptions = seller_product_status_options();
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$capabilities = seller_capabilities();
$pageTitle = 'Seller Products';
$pageDescription = 'Manage your seller listings, stock levels, and product visibility.';
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
        <p class="eyebrow">Seller products</p>
        <h1>Catalog control for your storefront</h1>
      </div>
      <a class="button" href="<?php echo htmlspecialchars(app_url('edit-product.php')); ?>">Add listing</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <?php if (!$capabilities['product_owner_support']): ?>
      <div class="notice">
        Product ownership is still missing from the shared seller schema. This page stays ready, but saves will only work once seller-linked products are available.
      </div>
    <?php endif; ?>
    <form class="card filter-form" method="get" action="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">
      <div class="field">
        <label for="search">Search listings</label>
        <input id="search" name="search" type="search" placeholder="Search title or category" value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="">All statuses</option>
          <?php foreach ($statusOptions as $status): ?>
            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $selectedStatus === $status ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars(seller_humanize_product_status($status)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-actions">
        <button class="button" type="submit">Apply filters</button>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">Clear</a>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Listings</p>
          <h2><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?></h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Listing</th><th>Category</th><th>Location</th><th>Status</th><th>Stock</th><th>Price</th><th>Updated</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($products === []): ?>
            <tr><td colspan="8">No listings match the current filters.</td></tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['title']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><?php echo htmlspecialchars($product['location'] !== '' ? $product['location'] : 'Not set'); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($product['status']); ?></span></td>
                <td><?php echo (int) $product['stock']; ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><?php echo htmlspecialchars($product['updated_on']); ?></td>
                <td><a class="text-link" href="<?php echo htmlspecialchars(app_url('edit-product.php?id=' . $product['id'])); ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
