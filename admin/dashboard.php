<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

$currentUser = app_require_admin();
$notices = admin_collect_notices();
$adminStats = market_get_admin_stats();
$recentOrders = market_get_admin_recent_orders(5);
$recentUsers = market_get_admin_recent_users(5);
$categories = market_get_categories();
$products = market_get_products(['limit' => 200]);
$pendingReviews = count(array_filter(admin_demo_rows('reviews', [
    ['id' => 1, 'status' => 'pending'],
    ['id' => 2, 'status' => 'approved'],
]), static fn (array $row): bool => ($row['status'] ?? '') === 'pending'));
$verificationRows = db_is_available() && market_table_exists('seller_profiles')
    ? market_get_seller_requests()
    : admin_demo_rows('verification', [
        ['id' => 1, 'verification_status' => 'pending'],
        ['id' => 2, 'verification_status' => 'approved'],
    ]);
$pendingVerification = count(array_filter($verificationRows, static function (array $row): bool {
    $status = strtolower((string) ($row['verification_status'] ?? $row['status'] ?? 'pending'));
    return $status === 'pending';
}));
$lowStock = 0;
foreach ($products as $product) {
    if ((int) ($product['stock'] ?? 0) <= 1) {
        $lowStock++;
    }
}
$quickStats = [
    ['label' => 'Categories', 'value' => (string) count($categories)],
    ['label' => 'Low stock', 'value' => (string) $lowStock],
    ['label' => 'Pending reviews', 'value' => (string) $pendingReviews],
    ['label' => 'Pending verification', 'value' => (string) $pendingVerification],
];
$modules = [
    ['label' => 'Users', 'path' => 'admin/users.php', 'description' => 'Update roles and disable access when required.'],
    ['label' => 'Categories', 'path' => 'admin/categories.php', 'description' => 'Keep the catalog structure tidy and remove empty groups.'],
    ['label' => 'Orders', 'path' => 'admin/orders.php', 'description' => 'Resolve payment state and fulfilment issues from one queue.'],
    ['label' => 'Reviews', 'path' => 'admin/reviews.php', 'description' => 'Moderate storefront feedback and queue items needing action.'],
    ['label' => 'Verification', 'path' => 'admin/verification.php', 'description' => 'Approve or reject seller verification requests.'],
    ['label' => 'Products', 'path' => 'admin/product-edit.php', 'description' => 'Edit listings, stock, and moderation status.'],
];
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Complete admin control centre for the LocalLink store.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('dashboard'); ?>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Admin dashboard</p>
        <h1>Store control centre</h1>
      </div>
      <div class="form-actions">
        <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">View store</a>
        <a class="button" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
      </div>
    </div>
    <?php admin_render_notices($notices); ?>
    <div class="grid stats">
      <?php foreach ($adminStats as $stat): ?>
        <article class="card stat-card"><span><?php echo htmlspecialchars($stat['label']); ?></span><strong><?php echo htmlspecialchars($stat['value']); ?></strong></article>
      <?php endforeach; ?>
    </div>
    <div class="grid stats">
      <?php foreach ($quickStats as $stat): ?>
        <article class="card stat-card"><span><?php echo htmlspecialchars($stat['label']); ?></span><strong><?php echo htmlspecialchars($stat['value']); ?></strong></article>
      <?php endforeach; ?>
    </div>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Operational scope</p>
        <h2>Each admin workflow now has its own route.</h2>
        <p>Use the sidebar to move between user access control, category maintenance, order updates, product moderation, reviews, and seller verification.</p>
      </div>
      <div class="feature-list">
        <div>Role and account status control</div>
        <div>Category create, rename, and safe delete flow</div>
        <div>Order payment and fulfilment updates</div>
        <div>Product editing plus archive-ready moderation</div>
      </div>
    </div>
    <section class="grid product-grid">
      <?php foreach ($modules as $module): ?>
        <article class="card stack">
          <p class="eyebrow"><?php echo htmlspecialchars($module['label']); ?></p>
          <h3><?php echo htmlspecialchars($module['label']); ?></h3>
          <p><?php echo htmlspecialchars($module['description']); ?></p>
          <div class="form-actions"><a class="button" href="<?php echo htmlspecialchars(app_url($module['path'])); ?>">Open</a></div>
        </article>
      <?php endforeach; ?>
    </section>
    <section class="card table-card">
      <div class="section-head">
        <div><p class="eyebrow">Recent orders</p><h2>Latest buyer orders</h2></div>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">Manage all orders</a>
      </div>
      <table>
        <thead><tr><th>Order</th><th>Buyer</th><th>Item</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
          <?php if ($recentOrders === []): ?>
            <tr><td colspan="7">No orders are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['code']); ?></td>
                <td><?php echo htmlspecialchars($order['buyer']); ?></td>
                <td><?php echo htmlspecialchars($order['item']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
                <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
                <td><?php echo htmlspecialchars($order['placed_on']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <section class="card table-card">
      <div class="section-head">
        <div><p class="eyebrow">Recent users</p><h2>Latest signups</h2></div>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/users.php')); ?>">Manage users</a>
      </div>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
        <tbody>
          <?php if ($recentUsers === []): ?>
            <tr><td colspan="5">No users are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentUsers as $user): ?>
              <tr>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($user['status']); ?></span></td>
                <td><?php echo htmlspecialchars($user['joined']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
