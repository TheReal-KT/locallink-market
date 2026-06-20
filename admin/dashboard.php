<?php
require dirname(__DIR__) . '/includes/app.php';
$currentUser = app_require_admin();
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$adminStats = market_get_admin_stats();
$recentOrders = market_get_admin_recent_orders(5);
$recentUsers = market_get_admin_recent_users(5);
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Simple admin dashboard for the store.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Admin dashboard</strong>
    <a class="is-active" href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Overview</a>
    <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
    <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">View store</a>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Admin dashboard</p>
        <h1>Store overview</h1>
      </div>
      <a class="button" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <div class="grid stats">
      <?php foreach ($adminStats as $stat): ?>
        <article class="card stat-card">
          <span><?php echo htmlspecialchars($stat['label']); ?></span>
          <strong><?php echo htmlspecialchars($stat['value']); ?></strong>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Simplified admin scope</p>
        <h2>Only the essentials remain.</h2>
        <p>The admin area focuses on product management, recent buyer activity, and checkout reporting from the relational order tables.</p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Manage</strong><span>Products and catalog counts</span></div>
        <div class="info-row"><strong>Review</strong><span>Recent orders, payment state, and user signups</span></div>
        <div class="info-row"><strong>Roles</strong><span>Buyer and Admin accounts</span></div>
      </div>
    </div>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Recent orders</p>
          <h2>Latest buyer orders</h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Order</th><th>Buyer</th><th>Item</th><th>Qty</th><th>Status</th><th>Payment</th><th>Delivery</th><th>Total</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php if ($recentOrders === []): ?>
            <tr><td colspan="9">No orders are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['code']); ?></td>
                <td><?php echo htmlspecialchars($order['buyer']); ?></td>
                <td><?php echo htmlspecialchars($order['item']); ?></td>
                <td><?php echo (int) $order['quantity']; ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
                <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                <td><?php echo htmlspecialchars($order['delivery_method']); ?></td>
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
        <div>
          <p class="eyebrow">Recent users</p>
          <h2>Latest signups</h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr>
        </thead>
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
