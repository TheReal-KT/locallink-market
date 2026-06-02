<?php
$pageTitle = 'Checkout';
$pageDescription = 'Place a LocalLink marketplace order.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Order checkout</p>
    <h1>Confirm delivery and payment.</h1>
    <p>Choose how you want to receive the item, select a payment method, and add any notes the seller should know.</p>
    <div class="support-grid support-grid-compact">
      <div class="support-card">
        <span>Order method</span>
        <strong>Choose a delivery option that works for both buyer and seller.</strong>
      </div>
      <div class="support-card">
        <span>Payment proof</span>
        <strong>Keep your payment reference or receipt until the seller confirms the order.</strong>
      </div>
    </div>
  </div>
  <form class="auth-form wide-form">
    <p class="eyebrow">Checkout form</p>
    <label for="delivery">Delivery method</label>
    <select id="delivery">
      <option>Standard delivery</option>
      <option>Express delivery</option>
    </select>
    <label for="payment">Payment method</label>
    <select id="payment">
      <option>EFT</option>
      <option>Cash</option>
      <option>Card</option>
    </select>
    <label for="notes">Order notes</label>
    <textarea id="notes" rows="5" placeholder="Preferred time, delivery details, or seller note"></textarea>
    <button class="button button-dark" type="button">Place order</button>
    <p class="form-note">Review your delivery and payment details before placing the order.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
