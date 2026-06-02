<?php
$pageTitle = 'Add Product';
$pageDescription = 'Create a new seller listing.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Seller listing</p>
    <h1>Add a product.</h1>
    <p>Add the key details buyers need: price, condition, category, and how the item can be collected or delivered.</p>
    <div class="support-grid support-grid-compact">
      <div class="support-card">
        <span>Listing quality</span>
        <strong>Use short titles, condition notes, and delivery guidance for clearer listings.</strong>
      </div>
      <div class="support-card">
        <span>Seller trust</span>
        <strong>Honest details and fair pricing help buyers feel confident about your item.</strong>
      </div>
    </div>
  </div>
  <form class="auth-form wide-form">
    <p class="eyebrow">Listing form</p>
    <label for="product-title">Product title</label>
    <input id="product-title" type="text" placeholder="Product name">
    <label for="category">Category</label>
    <select id="category">
      <option>Phones</option>
      <option>Fashion</option>
      <option>Homeware</option>
      <option>Study</option>
    </select>
    <label for="price">Price</label>
    <input id="price" type="text" placeholder="R 0.00">
    <label for="description">Description</label>
    <textarea id="description" rows="5" placeholder="Condition, delivery notes, and included items"></textarea>
    <button class="button button-dark" type="button">Save listing</button>
    <p class="form-note">Include what is in the box, any marks or wear, and the best way for buyers to receive the item.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
