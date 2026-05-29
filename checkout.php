<?php
$pageTitle = 'Checkout';
$pageDescription = 'Place a LocalLink marketplace order.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Order checkout</p>
    <h1>Confirm collection and payment.</h1>
    <p>The MVP checkout supports collection, local delivery, EFT, cash, and a mock card option.</p>
  </div>
  <form class="auth-form wide-form">
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
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
