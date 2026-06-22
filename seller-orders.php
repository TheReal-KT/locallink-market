<?php
require __DIR__ . '/includes/seller_tools.php';

$sellerUser = seller_require_account();
$search = trim((string) ($_GET['search'] ?? ''));
$selectedStatus = strtolower(trim((string) ($_GET['status'] ?? '')));
$orders = seller_get_orders_for_user($sellerUser, [
    'search' => $search,
    'status' => $selectedStatus,
]);
$statusOptions = seller_order_status_options();
$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$capabilities = seller_capabilities();
$pageTitle = 'Seller Orders';
$pageDescription = 'Track received orders and move them through the seller workflow.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong>Seller workspace</strong>
    <?php foreach (seller_navigation_items() as $item): ?>
      <a class="<?php echo $item['key'] === 'orders' ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['path'])); ?>">
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Seller orders</p>
        <h1>Keep buyers updated as orders move</h1>
      </div>
      <a class="button" href="<?php echo htmlspecialchars(app_url('seller-products.php')); ?>">View listings</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <?php if (!$capabilities['order_support']): ?>
      <div class="notice">
        Seller-linked orders are still waiting on the shared schema. The queue below is preview data until orders can be tied back to this seller account.
      </div>
    <?php endif; ?>
    <form class="card filter-form" method="get" action="<?php echo htmlspecialchars(app_url('seller-orders.php')); ?>">
      <div class="field">
        <label for="search">Search orders</label>
        <input id="search" name="search" type="search" placeholder="Order number, buyer, or item" value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="">All statuses</option>
          <?php foreach ($statusOptions as $status): ?>
            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $selectedStatus === $status ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars(seller_humanize_order_status($status)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-actions">
        <button class="button" type="submit">Apply filters</button>
        <a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-orders.php')); ?>">Clear</a>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Queue</p>
          <h2><?php echo count($orders); ?> order<?php echo count($orders) === 1 ? '' : 's'; ?></h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Order</th><th>Buyer</th><th>Items</th><th>Qty</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($orders === []): ?>
            <tr><td colspan="9">No orders match the current filters.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['code']); ?></td>
                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['item_summary']); ?></td>
                <td><?php echo (int) $order['quantity_total']; ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($order['status']); ?></span></td>
                <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
                <td><?php echo htmlspecialchars($order['placed_on']); ?></td>
                <td><a class="text-link" href="<?php echo htmlspecialchars(app_url('seller-order.php?id=' . $order['id'])); ?>">Open</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
