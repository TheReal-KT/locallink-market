<?php
$pageTitle = 'Checkout';
$pageDescription = 'Place a LocalLink marketplace order.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Order checkout</p>
    <h1>Confirm collection, delivery, and payment.</h1>
    <p>The MVP checkout supports collection, local delivery, EFT, cash, and a mock card option, using a layout that matches the Wonder checkout direction.</p>
    <div class="support-grid support-grid-compact">
      <div class="support-card">
        <span>Order method</span>
        <strong>Collection remains the default for a realistic C2C MVP.</strong>
      </div>
      <div class="support-card">
        <span>Payment proof</span>
        <strong>EFT references and proof-of-payment uploads are still planned backend work.</strong>
      </div>
    </div>
  </div>
  <form class="auth-form wide-form">
    <p class="eyebrow">Checkout form</p>
    <label for="delivery">Delivery method</label>
    <select id="delivery">
      <option>Collection</option>
      <option>Local delivery</option>
    </select>
    <label for="payment">Payment method</label>
    <select id="payment">
      <option>EFT</option>
      <option>Cash on collection</option>
      <option>Mock card</option>
    </select>
    <label for="notes">Order notes</label>
    <textarea id="notes" rows="5" placeholder="Preferred time, pickup details, or seller note"></textarea>
    <button class="button button-dark" type="button">Place order</button>
    <p class="form-note">Order creation, payment status changes, and history updates still need server-side implementation.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
