<?php
$pageTitle = 'Add Product';
$pageDescription = 'Create a new seller listing.';
require __DIR__ . '/includes/header.php';
?>
<section class="form-layout">
  <div>
    <p class="eyebrow">Seller listing</p>
    <h1>Add a product.</h1>
    <p>Simple fields now, ready for PHP validation and image upload handling later.</p>
  </div>
  <form class="auth-form wide-form">
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
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
