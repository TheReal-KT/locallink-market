<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_login();
app_set_account_mode('seller');
$sellerStats = market_get_seller_stats();
$sellerProducts = market_get_seller_products(6);
$sellerOrders = market_get_seller_orders(6);
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$pageTitle = 'Seller Dashboard';
$pageDescription = 'Seller view for listings, stock, and received orders.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Seller account</strong>
    <a class="is-active" href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Seller overview</a>
    <a href="<?php echo htmlspecialchars(app_url('switch-mode.php?mode=buyer')); ?>">Switch to buyer</a>
    <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">View store</a>
    <?php if (app_is_admin($currentUser)): ?>
      <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
      <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Admin dashboard</a>
    <?php endif; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Seller view</p>
        <h1>Listings and received orders</h1>
      </div>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('switch-mode.php?mode=buyer')); ?>">Buyer view</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <div class="grid stats">
      <?php foreach ($sellerStats as $stat): ?>
        <article class="card stat-card">
          <span><?php echo htmlspecialchars($stat['label']); ?></span>
          <strong><?php echo htmlspecialchars($stat['value']); ?></strong>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Mode switch</p>
        <h2><?php echo htmlspecialchars($currentUser['full_name']); ?></h2>
        <p>Use buyer view to shop and checkout. Use seller view to review active listings, stock, and order demand.</p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Current view</strong><span>Seller</span></div>
        <div class="info-row"><strong>Store email</strong><span><?php echo htmlspecialchars($currentUser['email']); ?></span></div>
      </div>
    </div>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Listings</p>
          <h2>Current stock</h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th></tr>
        </thead>
        <tbody>
          <?php if ($sellerProducts === []): ?>
            <tr><td colspan="4">No listings are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($sellerProducts as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['title']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($product['stock_label']); ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Orders</p>
          <h2>Recent received orders</h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Order</th><th>Customer</th><th>Item</th><th>Qty</th><th>Status</th><th>Total</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php if ($sellerOrders === []): ?>
            <tr><td colspan="7">No received orders are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($sellerOrders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['code']); ?></td>
                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                <td><?php echo htmlspecialchars($order['item']); ?></td>
                <td><?php echo (int) $order['quantity']; ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
                <td><?php echo htmlspecialchars($order['placed_on']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
