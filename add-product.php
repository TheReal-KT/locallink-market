<?php
$pageTitle = 'Add Product';
$pageDescription = 'Create a new seller listing.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Seller listing</p>
    <h1>Add a product.</h1>
    <p>Simple fields now, ready for PHP validation, seller ownership checks, and image upload handling later.</p>
    <div class="support-grid support-grid-compact">
      <div class="support-card">
        <span>Listing quality</span>
        <strong>Use short titles, condition notes, and pickup guidance for stronger buyer trust.</strong>
      </div>
      <div class="support-card">
        <span>Moderation ready</span>
        <strong>Product approval and hidden status can map directly to the planned admin flow.</strong>
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
    <textarea id="description" rows="5" placeholder="Condition, pickup notes, and included items"></textarea>
    <button class="button button-dark" type="button">Save listing</button>
    <p class="form-note">Image uploads, stock checks, and seller-only access controls are still pending.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
