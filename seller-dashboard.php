<?php
require __DIR__ . '/includes/seller_tools.php';

$sellerUser = seller_require_account();
$sellerStats = seller_get_dashboard_stats($sellerUser);
$recentProducts = seller_get_products_for_user($sellerUser, ['limit' => 4]);
$recentOrders = seller_get_orders_for_user($sellerUser, ['limit' => 5]);
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$capabilities = seller_capabilities();
$pageTitle = 'Seller Dashboard';
$pageDescription = 'Seller workspace for listings, received orders, and verification progress.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Seller workspace</strong>
    <?php foreach (seller_navigation_items() as $item): ?>
      <a class="<?php echo $item['key'] === 'overview' ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['path'])); ?>">
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Seller dashboard</p>
        <h1>Run your storefront from one place</h1>
      </div>
      <a class="button" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">Manage listings</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <?php if (!$capabilities['product_owner_support']): ?>
      <div class="notice">
        Seller listing ownership is still waiting for shared schema support. The dashboard shows preview data until `products.seller_id` is available.
      </div>
    <?php endif; ?>
    <?php if (!$capabilities['order_support']): ?>
      <div class="notice">
        Seller order routing is still pending in the shared schema. Order widgets stay usable with preview data until seller-linked orders are available.
      </div>
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
        <p class="eyebrow">Store profile</p>
        <h2><?php echo htmlspecialchars($sellerUser['seller_display_name']); ?></h2>
        <p><?php echo htmlspecialchars((string) $sellerUser['email']); ?></p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Verification</strong><span><?php echo htmlspecialchars($sellerUser['verification_label']); ?></span></div>
        <div class="info-row"><strong>Location</strong><span><?php echo htmlspecialchars($sellerUser['location'] !== '' ? $sellerUser['location'] : 'Add your trading location'); ?></span></div>
        <div class="info-row"><strong>Joined</strong><span><?php echo htmlspecialchars($sellerUser['joined_on']); ?></span></div>
      </div>
    </div>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Listings</p>
          <h2>Recently updated products</h2>
        </div>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">View all</a>
      </div>
      <table>
        <thead>
          <tr><th>Listing</th><th>Category</th><th>Status</th><th>Stock</th><th>Price</th><th>Updated</th></tr>
        </thead>
        <tbody>
          <?php if ($recentProducts === []): ?>
            <tr><td colspan="6">No listings yet. Add your first product from the seller products page.</td></tr>
          <?php else: ?>
            <?php foreach ($recentProducts as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['title']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($product['status']); ?></span></td>
                <td><?php echo (int) $product['stock']; ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><?php echo htmlspecialchars($product['updated_on']); ?></td>
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
          <h2>Latest received orders</h2>
        </div>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-orders.php')); ?>">Manage orders</a>
      </div>
      <table>
        <thead>
          <tr><th>Order</th><th>Buyer</th><th>Items</th><th>Status</th><th>Payment</th><th>Total</th></tr>
        </thead>
        <tbody>
          <?php if ($recentOrders === []): ?>
            <tr><td colspan="6">No orders have reached this seller account yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td><a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-order.php?id=' . $order['id'])); ?>"><?php echo htmlspecialchars($order['code']); ?></a></td>
                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['item_summary']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
                <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
