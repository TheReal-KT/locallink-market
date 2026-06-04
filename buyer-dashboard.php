<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_login();
$orders = market_get_buyer_orders((int) $currentUser['id']);
$buyerStats = market_get_buyer_stats((int) $currentUser['id']);
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$pageTitle = 'My Account';
$pageDescription = 'Customer account and order history.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Customer account</strong>
    <a class="is-active" href="<?php echo htmlspecialchars(app_url('buyer-dashboard.php')); ?>">Orders</a>
    <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
    <?php if (app_is_admin($currentUser)): ?>
      <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Admin dashboard</a>
    <?php endif; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">My account</p>
        <h1>Order history and account summary</h1>
      </div>
      <a class="button" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Shop now</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <div class="grid stats">
      <?php foreach ($buyerStats as $stat): ?>
        <div class="card stat-card"><span><?php echo htmlspecialchars($stat['label']); ?></span><strong><?php echo htmlspecialchars($stat['value']); ?></strong></div>
      <?php endforeach; ?>
    </div>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Account details</p>
        <h2><?php echo htmlspecialchars($currentUser['full_name']); ?></h2>
        <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Role</strong><span><?php echo app_is_admin($currentUser) ? 'Admin' : 'Customer'; ?></span></div>
        <div class="info-row"><strong>Joined</strong><span><?php echo htmlspecialchars(market_format_date((string) ($currentUser['created_at'] ?? ''))); ?></span></div>
      </div>
    </div>
    <div class="card table-card">
      <table>
        <thead>
          <tr><th>Order</th><th>Item</th><th>Qty</th><th>Status</th><th>Total</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php if ($orders === []): ?>
            <tr><td colspan="6">No orders yet. Place your first order from the product listing page.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['code']); ?></td>
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
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
