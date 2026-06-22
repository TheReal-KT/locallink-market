<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_buyer();

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
    'contact_name' => (string) ($currentUser['full_name'] ?? ''),
    'phone_number' => '',
    'address_line_1' => '',
    'address_line_2' => '',
    'city' => '',
    'postal_code' => '',
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
    $form['contact_name'] = trim((string) ($_POST['contact_name'] ?? ''));
    $form['phone_number'] = preg_replace('/\D+/', '', (string) ($_POST['phone_number'] ?? ''));
    $form['address_line_1'] = trim((string) ($_POST['address_line_1'] ?? ''));
    $form['address_line_2'] = trim((string) ($_POST['address_line_2'] ?? ''));
    $form['city'] = trim((string) ($_POST['city'] ?? ''));
    $form['postal_code'] = trim((string) ($_POST['postal_code'] ?? ''));
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

    if ($form['contact_name'] === '') {
        $errors[] = 'Enter the customer contact name.';
    }

    if (strlen($form['phone_number']) < 10 || strlen($form['phone_number']) > 15) {
        $errors[] = 'Enter a valid contact phone number.';
    }

    if ($form['delivery'] !== 'collection') {
        if ($form['address_line_1'] === '') {
            $errors[] = 'Enter the delivery address line 1.';
        }

        if ($form['city'] === '') {
            $errors[] = 'Enter the delivery city.';
        }

        if ($form['postal_code'] === '') {
            $errors[] = 'Enter the delivery postal code.';
        }
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
            $orderNumber = market_create_order([
                'user_id' => (int) $currentUser['id'],
                'product_id' => (int) $product['id'],
                'quantity' => (int) $form['quantity'],
                'delivery_method' => $form['delivery'],
                'payment_method' => $form['payment'],
                'buyer_note' => $form['notes'],
                'contact_name' => $form['contact_name'],
                'phone_number' => $form['phone_number'],
                'address_line_1' => $form['address_line_1'],
                'address_line_2' => $form['address_line_2'],
                'city' => $form['city'],
                'postal_code' => $form['postal_code'],
            ]);
            app_set_flash('success', 'Order #' . $orderNumber . ' was placed successfully.');
            app_redirect('buyer-dashboard.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
$pageDescription = 'Review your product, customer details, and simulated payment.';
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
          <p class="eyebrow">2. Delivery contact</p>
          <h2>Customer and handover details</h2>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="contact-name">Contact name</label>
            <input id="contact-name" name="contact_name" type="text" placeholder="Customer name" value="<?php echo htmlspecialchars($form['contact_name']); ?>">
          </div>
          <div class="field">
            <label for="phone-number">Phone number</label>
            <input id="phone-number" name="phone_number" type="tel" inputmode="tel" placeholder="0812345678" value="<?php echo htmlspecialchars($form['phone_number']); ?>">
          </div>
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
            <span>Collect from store</span>
            <strong>Free</strong>
          </label>
        </div>
        <div class="field">
          <label for="address-line-1">Address line 1</label>
          <input id="address-line-1" name="address_line_1" type="text" placeholder="Required for delivery orders" value="<?php echo htmlspecialchars($form['address_line_1']); ?>">
        </div>
        <div class="field">
          <label for="address-line-2">Address line 2</label>
          <input id="address-line-2" name="address_line_2" type="text" placeholder="Apartment, room, or landmark" value="<?php echo htmlspecialchars($form['address_line_2']); ?>">
        </div>
        <div class="field-row">
          <div class="field">
            <label for="city">City</label>
            <input id="city" name="city" type="text" placeholder="Johannesburg" value="<?php echo htmlspecialchars($form['city']); ?>">
          </div>
          <div class="field">
            <label for="postal-code">Postal code</label>
            <input id="postal-code" name="postal_code" type="text" placeholder="2000" value="<?php echo htmlspecialchars($form['postal_code']); ?>">
          </div>
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
            <strong>Marks payment as paid</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="payment" value="eft" <?php echo $form['payment'] === 'eft' ? 'checked' : ''; ?>>
            <span>EFT</span>
            <strong>Awaits confirmation</strong>
          </label>
          <label class="choice-card">
            <input type="radio" name="payment" value="cash" <?php echo $form['payment'] === 'cash' ? 'checked' : ''; ?>>
            <span>Cash</span>
            <strong>Pay on handover</strong>
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
          <h2>Order notes</h2>
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
      <div class="info-row"><strong>Delivery type</strong><span><?php echo htmlspecialchars(market_humanize_delivery_method($form['delivery'])); ?></span></div>
      <div class="info-row"><strong>Payment mode</strong><span><?php echo htmlspecialchars(market_humanize_payment_method($form['payment'])); ?></span></div>
    </div>
    <div class="checkout-steps">
      <span>Review</span>
      <span>Contact</span>
      <span>Payment</span>
      <span>Saved order</span>
    </div>
  </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
