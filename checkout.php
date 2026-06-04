<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_login();

$productId = max(0, (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0));
if ($productId < 1) {
    $fallbackProducts = market_get_products(['limit' => 1]);
    $productId = (int) ($fallbackProducts[0]['id'] ?? 0);
}

$product = $productId > 0 ? market_get_product_by_id($productId) : null;
$errors = [];
$form = [
    'delivery' => 'standard_delivery',
    'payment' => 'eft',
    'quantity' => '1',
    'notes' => '',
];

if (app_is_post_request()) {
    $form['delivery'] = (string) ($_POST['delivery'] ?? 'standard_delivery');
    $form['payment'] = (string) ($_POST['payment'] ?? 'eft');
    $form['quantity'] = trim((string) ($_POST['quantity'] ?? '1'));
    $form['notes'] = trim((string) ($_POST['notes'] ?? ''));

    if ($product === null) {
        $errors[] = 'Choose a valid product before placing the order.';
    }

    if (!in_array($form['delivery'], ['collection', 'standard_delivery', 'express_delivery'], true)) {
        $errors[] = 'Choose a valid delivery method.';
    }

    if (!in_array($form['payment'], ['eft', 'cash', 'card'], true)) {
        $errors[] = 'Choose a valid payment method.';
    }

    if (!ctype_digit($form['quantity']) || (int) $form['quantity'] < 1) {
        $errors[] = 'Quantity must be at least 1.';
    }

    if ($product !== null && (int) $form['quantity'] > (int) $product['stock']) {
        $errors[] = 'Quantity cannot be more than the available stock.';
    }

    if ($errors === []) {
        try {
            $orderNumber = market_create_order(
                (int) $currentUser['id'],
                (int) $product['id'],
                (int) $form['quantity'],
                $form['delivery'],
                $form['payment'],
                $form['notes']
            );
            app_set_flash('success', 'Order #' . $orderNumber . ' was placed successfully.');
            app_redirect('buyer-dashboard.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
$pageDescription = 'Review your product and place the order.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card stack">
    <p class="eyebrow">Checkout</p>
    <h1>Place your order</h1>
    <p>Choose quantity, delivery, and payment method, then submit the order to save it on your dashboard.</p>
    <?php if ($product !== null): ?>
      <div class="detail-list">
        <div class="info-row"><strong>Product</strong><span><?php echo htmlspecialchars($product['title']); ?></span></div>
        <div class="info-row"><strong>Price</strong><span><?php echo htmlspecialchars($product['price']); ?></span></div>
        <div class="info-row"><strong>Stock</strong><span><?php echo htmlspecialchars($product['stock_label']); ?></span></div>
      </div>
    <?php endif; ?>
  </div>
  <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('checkout.php')); ?>">
    <p class="eyebrow">Order form</p>
    <?php if ($errors !== []): ?>
      <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($product === null): ?>
      <div class="notice notice-error">No product is selected for checkout. Start from a product page.</div>
    <?php endif; ?>
    <input type="hidden" name="product_id" value="<?php echo (int) ($product['id'] ?? 0); ?>">
    <div class="field">
      <label for="quantity">Quantity</label>
      <input id="quantity" name="quantity" type="number" min="1" max="<?php echo (int) ($product['stock'] ?? 1); ?>" value="<?php echo htmlspecialchars($form['quantity']); ?>">
    </div>
    <div class="field">
      <label for="delivery">Delivery method</label>
      <select id="delivery" name="delivery">
        <option value="standard_delivery" <?php echo $form['delivery'] === 'standard_delivery' ? 'selected' : ''; ?>>Standard delivery</option>
        <option value="express_delivery" <?php echo $form['delivery'] === 'express_delivery' ? 'selected' : ''; ?>>Express delivery</option>
        <option value="collection" <?php echo $form['delivery'] === 'collection' ? 'selected' : ''; ?>>Collection</option>
      </select>
    </div>
    <div class="field">
      <label for="payment">Payment method</label>
      <select id="payment" name="payment">
        <option value="eft" <?php echo $form['payment'] === 'eft' ? 'selected' : ''; ?>>EFT</option>
        <option value="cash" <?php echo $form['payment'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
        <option value="card" <?php echo $form['payment'] === 'card' ? 'selected' : ''; ?>>Card</option>
      </select>
    </div>
    <div class="field">
      <label for="notes">Order notes</label>
      <textarea id="notes" name="notes" rows="5" placeholder="Preferred time or delivery note"><?php echo htmlspecialchars($form['notes']); ?></textarea>
    </div>
    <button class="button" type="submit" <?php echo $product === null || (($product['stock'] ?? 0) < 1) ? 'disabled' : ''; ?>>Place order</button>
    <p class="hint">Orders are saved to your customer dashboard after checkout.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
