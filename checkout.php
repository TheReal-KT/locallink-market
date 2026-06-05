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
    'payment' => 'card',
    'quantity' => '1',
    'notes' => '',
    'card_name' => '',
    'card_number' => '',
    'card_expiry' => '',
    'card_cvv' => '',
];

if (app_is_post_request()) {
    $form['delivery'] = (string) ($_POST['delivery'] ?? 'standard_delivery');
    $form['payment'] = (string) ($_POST['payment'] ?? 'card');
    $form['quantity'] = trim((string) ($_POST['quantity'] ?? '1'));
    $form['notes'] = trim((string) ($_POST['notes'] ?? ''));
    $form['card_name'] = trim((string) ($_POST['card_name'] ?? ''));
    $form['card_number'] = preg_replace('/\D+/', '', (string) ($_POST['card_number'] ?? ''));
    $form['card_expiry'] = trim((string) ($_POST['card_expiry'] ?? ''));
    $form['card_cvv'] = preg_replace('/\D+/', '', (string) ($_POST['card_cvv'] ?? ''));

    if ($product === null) {
        $errors[] = 'Choose a valid product before placing the order.';
    }

    if (!in_array($form['delivery'], ['collection', 'standard_delivery', 'express_delivery'], true)) {
        $errors[] = 'Choose a valid delivery method.';
    }

    if (!in_array($form['payment'], ['eft', 'cash', 'card'], true)) {
        $errors[] = 'Choose a valid payment method.';
    }

    if ($form['payment'] === 'card') {
        if ($form['card_name'] === '') {
            $errors[] = 'Enter the name on the card.';
        }

        if (strlen($form['card_number']) < 12 || strlen($form['card_number']) > 19) {
            $errors[] = 'Enter a valid card number for the simulated payment.';
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $form['card_expiry'])) {
            $errors[] = 'Use MM/YY for the card expiry.';
        }

        if (strlen($form['card_cvv']) < 3 || strlen($form['card_cvv']) > 4) {
            $errors[] = 'Enter a valid CVV.';
        }
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
$unitPrice = (float) ($product['price_amount'] ?? 0);
$quantity = ctype_digit($form['quantity']) ? max(1, (int) $form['quantity']) : 1;
$subtotal = $unitPrice * $quantity;
$deliveryFee = market_delivery_fee($form['delivery']);
$orderTotal = $subtotal + $deliveryFee;
require __DIR__ . '/includes/header.php';
?>
<section class="page section checkout-shell">
  <div class="checkout-main stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Checkout</p>
        <h1>Payment and delivery</h1>
      </div>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Keep shopping</a>
    </div>
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
    <form class="card form-card checkout-form" method="post" action="<?php echo htmlspecialchars(app_url('checkout.php')); ?>" data-checkout-form data-unit-price="<?php echo htmlspecialchars((string) $unitPrice); ?>">
      <input type="hidden" name="product_id" value="<?php echo (int) ($product['id'] ?? 0); ?>">
      <section class="checkout-block">
        <div>
          <p class="eyebrow">1. Order details</p>
          <h2>Confirm your item</h2>
        </div>
        <?php if ($product !== null): ?>
          <div class="checkout-product">
            <img src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
            <div>
              <strong><?php echo htmlspecialchars($product['title']); ?></strong>
              <span><?php echo htmlspecialchars($product['category']); ?></span>
              <span><?php echo htmlspecialchars($product['stock_label']); ?></span>
            </div>
          </div>
        <?php endif; ?>
        <div class="field">
          <label for="quantity">Quantity</label>
          <input id="quantity" name="quantity" type="number" min="1" max="<?php echo (int) ($product['stock'] ?? 1); ?>" value="<?php echo htmlspecialchars($form['quantity']); ?>" data-quantity>
        </div>
      </section>

      <section class="checkout-block">
        <div>
          <p class="eyebrow">2. Delivery</p>
          <h2>Choose handover</h2>
        </div>
        <div class="choice-grid">
          <label class="choice-card">
            <input type="radio" name="delivery" value="standard_delivery" data-delivery-fee="45" <?php echo $form['delivery'] === 'standard_delivery' ? 'checked' : ''; ?>>
            <span>Standard delivery</span>
            <strong>R 45.00</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="delivery" value="express_delivery" data-delivery-fee="85" <?php echo $form['delivery'] === 'express_delivery' ? 'checked' : ''; ?>>
            <span>Express delivery</span>
            <strong>R 85.00</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="delivery" value="collection" data-delivery-fee="0" <?php echo $form['delivery'] === 'collection' ? 'checked' : ''; ?>>
            <span>Collect from seller</span>
            <strong>Free</strong>
          </label>
        </div>
      </section>

      <section class="checkout-block">
        <div>
          <p class="eyebrow">3. Payment</p>
          <h2>Simulate payment</h2>
        </div>
        <div class="choice-grid">
          <label class="choice-card">
            <input type="radio" name="payment" value="card" <?php echo $form['payment'] === 'card' ? 'checked' : ''; ?>>
            <span>Mock card</span>
            <strong>Instant</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="payment" value="eft" <?php echo $form['payment'] === 'eft' ? 'checked' : ''; ?>>
            <span>EFT</span>
            <strong>Manual</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="payment" value="cash" <?php echo $form['payment'] === 'cash' ? 'checked' : ''; ?>>
            <span>Cash</span>
            <strong>On handover</strong>
          </label>
        </div>
        <div class="payment-card">
          <div class="field">
            <label for="card-name">Name on card</label>
            <input id="card-name" name="card_name" type="text" placeholder="Nandi Partner" value="<?php echo htmlspecialchars($form['card_name']); ?>">
          </div>
          <div class="field">
            <label for="card-number">Card number</label>
            <input id="card-number" name="card_number" type="text" inputmode="numeric" placeholder="4242 4242 4242 4242" value="<?php echo htmlspecialchars($form['card_number']); ?>">
          </div>
          <div class="field-row">
            <div class="field">
              <label for="card-expiry">Expiry</label>
              <input id="card-expiry" name="card_expiry" type="text" placeholder="MM/YY" value="<?php echo htmlspecialchars($form['card_expiry']); ?>">
            </div>
            <div class="field">
              <label for="card-cvv">CVV</label>
              <input id="card-cvv" name="card_cvv" type="text" inputmode="numeric" placeholder="123" value="<?php echo htmlspecialchars($form['card_cvv']); ?>">
            </div>
          </div>
          <p class="hint">This is a simulated checkout for the project. No real card is charged.</p>
        </div>
      </section>

      <section class="checkout-block">
        <div>
          <p class="eyebrow">4. Notes</p>
          <h2>Message the seller</h2>
        </div>
        <div class="field">
          <label for="notes">Order notes</label>
          <textarea id="notes" name="notes" rows="4" placeholder="Preferred delivery time or collection note"><?php echo htmlspecialchars($form['notes']); ?></textarea>
        </div>
        <button class="button" type="submit" <?php echo $product === null || (($product['stock'] ?? 0) < 1) ? 'disabled' : ''; ?>>Pay and place order</button>
      </section>
    </form>
  </div>

  <aside class="card checkout-summary" aria-label="Order summary">
    <p class="eyebrow">Order summary</p>
    <div class="summary-total">
      <span>Total</span>
      <strong data-order-total><?php echo htmlspecialchars(market_format_money($orderTotal)); ?></strong>
    </div>
    <div class="detail-list">
      <div class="info-row"><strong>Item price</strong><span><?php echo htmlspecialchars($product['price'] ?? market_format_money(0)); ?></span></div>
      <div class="info-row"><strong>Subtotal</strong><span data-subtotal><?php echo htmlspecialchars(market_format_money($subtotal)); ?></span></div>
      <div class="info-row"><strong>Delivery</strong><span data-delivery-total><?php echo htmlspecialchars(market_format_money($deliveryFee)); ?></span></div>
      <div class="info-row"><strong>Payment status</strong><span>Simulated</span></div>
    </div>
    <div class="checkout-steps">
      <span>Review</span>
      <span>Delivery</span>
      <span>Payment</span>
      <span>Saved order</span>
    </div>
  </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
