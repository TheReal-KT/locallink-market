<?php
require __DIR__ . '/includes/seller_tools.php';

$sellerUser = seller_require_account();
$orderId = isset($_GET['id']) ? max(0, (int) $_GET['id']) : 0;
$order = $orderId > 0 ? seller_get_order_for_user($sellerUser, $orderId) : null;
$statusOptions = seller_order_status_options();
$capabilities = seller_capabilities();

if ($order === null) {
    http_response_code(404);
    $pageTitle = 'Order not found';
    $pageDescription = 'The seller order you requested could not be found.';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page section">
      <div class="card stack">
        <p class="eyebrow">Order unavailable</p>
        <h1>This seller order could not be found.</h1>
        <p>The order may belong to a different seller or the shared seller schema may not be ready yet.</p>
        <a class="button" href="<?php echo htmlspecialchars(app_url('seller-orders.php')); ?>">Back to orders</a>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    return;
}

if (app_is_post_request()) {
    try {
        seller_update_order_status($sellerUser, $orderId, (string) ($_POST['status'] ?? 'pending'));
        app_set_flash('success', 'Order status updated successfully.');
        app_redirect('seller-order.php?id=' . $orderId);
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
        app_redirect('seller-order.php?id=' . $orderId);
    }
}

$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$pageTitle = 'Seller Order';
$pageDescription = 'Review seller order details and update the order status.';
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
        <p class="eyebrow">Seller order</p>
        <h1><?php echo htmlspecialchars($order['code']); ?></h1>
      </div>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('seller-orders.php')); ?>">Back to orders</a>
    </div>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <?php if (!$capabilities['order_support']): ?>
      <div class="notice">
        This order page is wired for the seller workflow, but real updates depend on seller-linked orders in the shared schema.
      </div>
    <?php endif; ?>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Buyer</p>
        <h2><?php echo htmlspecialchars($order['buyer_name']); ?></h2>
        <p><?php echo htmlspecialchars($order['buyer_email'] !== '' ? $order['buyer_email'] : 'No buyer email stored'); ?></p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Status</strong><span><?php echo htmlspecialchars($order['status']); ?></span></div>
        <div class="info-row"><strong>Payment</strong><span><?php echo htmlspecialchars($order['payment_status'] . ' via ' . $order['payment_method']); ?></span></div>
        <div class="info-row"><strong>Total</strong><span><?php echo htmlspecialchars($order['total']); ?></span></div>
        <div class="info-row"><strong>Placed</strong><span><?php echo htmlspecialchars($order['placed_on']); ?></span></div>
      </div>
    </div>
    <section class="card table-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Items</p>
          <h2>Ordered products</h2>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr>
        </thead>
        <tbody>
          <?php foreach ($order['items'] as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['title']); ?></td>
              <td><?php echo (int) $item['quantity']; ?></td>
              <td><?php echo htmlspecialchars($item['unit_price']); ?></td>
              <td><?php echo htmlspecialchars($item['line_total']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Delivery</p>
        <h2><?php echo htmlspecialchars($order['delivery_method']); ?></h2>
        <div class="detail-list">
          <div class="info-row"><strong>Contact</strong><span><?php echo htmlspecialchars($order['contact_name'] !== '' ? $order['contact_name'] : 'Not stored'); ?></span></div>
          <div class="info-row"><strong>Phone</strong><span><?php echo htmlspecialchars($order['phone_number'] !== '' ? $order['phone_number'] : 'Not stored'); ?></span></div>
          <div class="info-row"><strong>Address</strong><span><?php echo htmlspecialchars(trim($order['address_line_1'] . ' ' . $order['address_line_2']) !== '' ? trim($order['address_line_1'] . ' ' . $order['address_line_2']) : 'Collection or address not stored'); ?></span></div>
          <div class="info-row"><strong>Area</strong><span><?php echo htmlspecialchars(trim($order['city'] . ' ' . $order['postal_code']) !== '' ? trim($order['city'] . ' ' . $order['postal_code']) : 'Not stored'); ?></span></div>
        </div>
      </div>
      <div class="stack">
        <p class="eyebrow">Seller action</p>
        <h2>Update the order status</h2>
        <form method="post" action="<?php echo htmlspecialchars(app_url('seller-order.php?id=' . $orderId)); ?>">
          <div class="field">
            <label for="status">Order status</label>
            <select id="status" name="status">
              <?php foreach ($statusOptions as $status): ?>
                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $order['raw_status'] === $status ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars(seller_humanize_order_status($status)); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-actions">
            <button class="button" type="submit">Update status</button>
          </div>
        </form>
        <div class="feature-list">
          <div>Buyer note: <?php echo htmlspecialchars($order['buyer_note'] !== '' ? $order['buyer_note'] : 'No note was saved with this order.'); ?></div>
          <div>Collection note: <?php echo htmlspecialchars($order['collection_note'] !== '' ? $order['collection_note'] : 'No collection note stored.'); ?></div>
          <div>Last updated view: <?php echo htmlspecialchars($order['updated_on']); ?></div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
