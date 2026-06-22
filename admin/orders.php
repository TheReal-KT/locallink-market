<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_order_rows(array $filters): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $rows = array_map(static function (array $row): array {
            return [
                'id' => 0,
                'order_number' => (string) $row['order_number'],
                'buyer' => (string) $row['buyer'],
                'items' => (string) $row['item'],
                'quantity' => (int) $row['quantity'],
                'status_key' => (string) $row['status'],
                'status' => market_humanize_order_status((string) $row['status']),
                'payment_status_key' => (string) $row['payment_status'],
                'payment_status' => market_humanize_payment_status((string) $row['payment_status']),
                'payment_method' => market_humanize_payment_method((string) ($row['payment_method'] ?? 'eft')),
                'delivery_method' => market_humanize_delivery_method((string) ($row['delivery_method'] ?? 'collection')),
                'total' => market_format_money((float) $row['total_amount']),
                'buyer_note' => '',
                'contact_name' => '',
                'city' => '',
                'created_at' => (string) $row['created_at'],
            ];
        }, market_sample_orders());
    } elseif (market_has_normalized_orders()) {
        $statement = $pdo->query('SELECT o.id, o.order_number, u.full_name AS buyer, COALESCE(GROUP_CONCAT(p.title ORDER BY oi.id SEPARATOR ", "), "Product") AS items, COALESCE(SUM(oi.quantity), 0) AS quantity, o.status, o.payment_status, COALESCE(MAX(op.payment_method), "eft") AS payment_method, o.delivery_method, o.total_amount, o.buyer_note, MAX(oa.contact_name) AS contact_name, MAX(oa.city) AS city, o.created_at FROM orders o INNER JOIN users u ON u.id = o.user_id LEFT JOIN order_items oi ON oi.order_id = o.id LEFT JOIN products p ON p.id = oi.product_id LEFT JOIN order_payments op ON op.order_id = o.id LEFT JOIN order_addresses oa ON oa.order_id = o.id GROUP BY o.id, o.order_number, u.full_name, o.status, o.payment_status, o.delivery_method, o.total_amount, o.buyer_note, o.created_at ORDER BY o.created_at DESC');
        $rows = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'order_number' => (string) $row['order_number'],
                'buyer' => (string) $row['buyer'],
                'items' => (string) $row['items'],
                'quantity' => (int) $row['quantity'],
                'status_key' => (string) $row['status'],
                'status' => market_humanize_order_status((string) $row['status']),
                'payment_status_key' => (string) $row['payment_status'],
                'payment_status' => market_humanize_payment_status((string) $row['payment_status']),
                'payment_method' => market_humanize_payment_method((string) $row['payment_method']),
                'delivery_method' => market_humanize_delivery_method((string) $row['delivery_method']),
                'total' => market_format_money((float) $row['total_amount']),
                'buyer_note' => (string) ($row['buyer_note'] ?? ''),
                'contact_name' => (string) ($row['contact_name'] ?? ''),
                'city' => (string) ($row['city'] ?? ''),
                'created_at' => (string) $row['created_at'],
            ];
        }, $statement->fetchAll());
    } else {
        $statement = $pdo->query('SELECT o.id, o.order_number, u.full_name AS buyer, p.title AS items, o.quantity, o.status, o.payment_method, o.delivery_method, o.total_amount, o.buyer_note, o.created_at FROM orders o INNER JOIN users u ON u.id = o.user_id INNER JOIN products p ON p.id = o.product_id ORDER BY o.created_at DESC');
        $rows = array_map(static function (array $row): array {
            $paymentStatusKey = market_simulated_payment_status((string) ($row['payment_method'] ?? 'eft'));
            return [
                'id' => (int) $row['id'],
                'order_number' => (string) $row['order_number'],
                'buyer' => (string) $row['buyer'],
                'items' => (string) $row['items'],
                'quantity' => (int) $row['quantity'],
                'status_key' => (string) $row['status'],
                'status' => market_humanize_order_status((string) $row['status']),
                'payment_status_key' => $paymentStatusKey,
                'payment_status' => market_humanize_payment_status($paymentStatusKey),
                'payment_method' => market_humanize_payment_method((string) ($row['payment_method'] ?? 'eft')),
                'delivery_method' => market_humanize_delivery_method((string) ($row['delivery_method'] ?? 'collection')),
                'total' => market_format_money((float) $row['total_amount']),
                'buyer_note' => (string) ($row['buyer_note'] ?? ''),
                'contact_name' => '',
                'city' => '',
                'created_at' => (string) $row['created_at'],
            ];
        }, $statement->fetchAll());
    }

    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $status = trim((string) ($filters['status'] ?? 'all'));
    $payment = trim((string) ($filters['payment'] ?? 'all'));

    return array_values(array_filter($rows, static function (array $row) use ($search, $status, $payment): bool {
        if ($status !== 'all' && $row['status_key'] !== $status) {
            return false;
        }
        if ($payment !== 'all' && $row['payment_status_key'] !== $payment) {
            return false;
        }
        if ($search === '') {
            return true;
        }
        return strpos(strtolower($row['order_number'] . ' ' . $row['buyer'] . ' ' . $row['items']), $search) !== false;
    }));
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    try {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'pending');
        $paymentStatus = (string) ($_POST['payment_status'] ?? 'pending');
        if (!in_array($status, ['pending', 'paid', 'completed', 'cancelled'], true)) {
            throw new RuntimeException('Choose a valid order status.');
        }
        if (!in_array($paymentStatus, ['pending', 'awaiting_confirmation', 'paid', 'failed'], true)) {
            throw new RuntimeException('Choose a valid payment status.');
        }
        $pdo = db_try_get_connection();
        if (!$pdo) {
            throw new RuntimeException(market_database_unavailable_message());
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('UPDATE orders SET status = :status, payment_status = :payment_status WHERE id = :order_id');
        $statement->execute(['order_id' => $orderId, 'status' => $status, 'payment_status' => $paymentStatus]);
        if (market_table_exists('order_payments')) {
            $payment = $pdo->prepare('UPDATE order_payments SET payment_status = :payment_status, paid_at = CASE WHEN :is_paid = 1 THEN COALESCE(paid_at, CURRENT_TIMESTAMP) ELSE NULL END WHERE order_id = :order_id');
            $payment->execute(['order_id' => $orderId, 'payment_status' => $paymentStatus, 'is_paid' => $paymentStatus === 'paid' ? 1 : 0]);
        }
        $pdo->commit();
        app_set_flash('success', 'Order updated.');
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_set_flash('error', $exception->getMessage());
    }

    admin_redirect('orders.php');
}

$filters = ['search' => trim((string) ($_GET['search'] ?? '')), 'status' => trim((string) ($_GET['status'] ?? 'all')), 'payment' => trim((string) ($_GET['payment'] ?? 'all'))];
$orders = admin_order_rows($filters);
$notices = admin_collect_notices();
$pageTitle = 'Admin Orders';
$pageDescription = 'Manage order progress and payment state.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('orders'); ?>
  <div class="stack">
    <div class="section-head"><div><p class="eyebrow">Order management</p><h1>Checkout operations</h1></div><p>Resolve payment confirmation, cancellations, and fulfilment progress from one queue.</p></div>
    <?php admin_render_notices($notices); ?>
    <form class="card" method="get" action="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">
      <div class="field-row">
        <div class="field"><label for="order-search">Search</label><input id="order-search" name="search" type="search" placeholder="Order or buyer" value="<?php echo htmlspecialchars($filters['search']); ?>"></div>
        <div class="field"><label for="order-status">Order status</label><select id="order-status" name="status"><option value="all">All order states</option><option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="paid" <?php echo $filters['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option><option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option><option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option></select></div>
      </div>
      <div class="field-row">
        <div class="field"><label for="payment-status">Payment status</label><select id="payment-status" name="payment"><option value="all">All payment states</option><option value="pending" <?php echo $filters['payment'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="awaiting_confirmation" <?php echo $filters['payment'] === 'awaiting_confirmation' ? 'selected' : ''; ?>>Awaiting confirmation</option><option value="paid" <?php echo $filters['payment'] === 'paid' ? 'selected' : ''; ?>>Paid</option><option value="failed" <?php echo $filters['payment'] === 'failed' ? 'selected' : ''; ?>>Failed</option></select></div>
        <div class="form-actions"><button class="button" type="submit">Apply filters</button><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">Clear</a></div>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">Order queue</p><h2><?php echo count($orders); ?> order<?php echo count($orders) === 1 ? '' : 's'; ?></h2></div></div>
      <table>
        <thead><tr><th>Order</th><th>Buyer</th><th>Items</th><th>Delivery</th><th>Total</th><th>Notes</th><th>Update</th></tr></thead>
        <tbody>
          <?php if ($orders === []): ?>
            <tr><td colspan="7">No orders matched the current filters.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong><br><span class="hint"><?php echo htmlspecialchars(admin_format_datetime($order['created_at'])); ?></span></td>
                <td><?php echo htmlspecialchars($order['buyer']); ?></td>
                <td><?php echo htmlspecialchars($order['items']); ?><br><span class="hint">Qty: <?php echo (int) $order['quantity']; ?></span></td>
                <td><?php echo htmlspecialchars($order['delivery_method']); ?><br><span class="hint"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
                <td><?php if ($order['contact_name'] !== ''): ?><strong><?php echo htmlspecialchars($order['contact_name']); ?></strong><br><?php endif; ?><?php if ($order['city'] !== ''): ?><span class="hint"><?php echo htmlspecialchars($order['city']); ?></span><br><?php endif; ?><?php echo htmlspecialchars($order['buyer_note'] !== '' ? $order['buyer_note'] : 'No buyer note'); ?></td>
                <td>
                  <form class="stack" method="post" action="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                    <div class="field"><label>Status</label><select name="status"><option value="pending" <?php echo $order['status_key'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="paid" <?php echo $order['status_key'] === 'paid' ? 'selected' : ''; ?>>Paid</option><option value="completed" <?php echo $order['status_key'] === 'completed' ? 'selected' : ''; ?>>Completed</option><option value="cancelled" <?php echo $order['status_key'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option></select></div>
                    <div class="field"><label>Payment</label><select name="payment_status"><option value="pending" <?php echo $order['payment_status_key'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="awaiting_confirmation" <?php echo $order['payment_status_key'] === 'awaiting_confirmation' ? 'selected' : ''; ?>>Awaiting confirmation</option><option value="paid" <?php echo $order['payment_status_key'] === 'paid' ? 'selected' : ''; ?>>Paid</option><option value="failed" <?php echo $order['payment_status_key'] === 'failed' ? 'selected' : ''; ?>>Failed</option></select></div>
                    <button class="button" type="submit">Save</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
