<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Buyer Dashboard';
$pageDescription = 'Buyer order overview and activity.';
require __DIR__ . '/includes/header.php';
?>
<section class="dashboard-layout">
  <aside class="dashboard-sidebar">
    <strong>Buyer workspace</strong>
    <a href="<?php echo htmlspecialchars(app_url('buyer-dashboard.php')); ?>">Orders</a>
    <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Saved searches</a>
    <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Reviews</a>
    <a href="<?php echo htmlspecialchars(app_url('login.php')); ?>">Account</a>
  </aside>
  <div class="dashboard-main">
    <div class="section-heading row-heading">
      <div>
        <p class="eyebrow">Buyer dashboard</p>
        <h1>Your orders at a glance.</h1>
      </div>
      <a class="button button-dark" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Shop again</a>
    </div>
    <div class="stat-grid">
      <div class="stat-card"><span>Open orders</span><strong>2</strong></div>
      <div class="stat-card"><span>Completed</span><strong>8</strong></div>
      <div class="stat-card"><span>Reviews due</span><strong>1</strong></div>
    </div>
    <div class="dashboard-callout">
      <span>Order updates</span>
      <p>Track delivery progress, payment details, and reviews from your buyer account.</p>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Order</th><th>Item</th><th>Seller</th><th>Status</th><th>Total</th></tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?php echo htmlspecialchars($order['code']); ?></td>
              <td><?php echo htmlspecialchars($order['item']); ?></td>
              <td><?php echo htmlspecialchars($order['seller']); ?></td>
              <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
              <td><?php echo htmlspecialchars($order['total']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
