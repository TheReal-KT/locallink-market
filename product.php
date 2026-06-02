<?php
require __DIR__ . '/includes/data.php';
$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$product = $products[0];
foreach ($products as $item) {
    if ($item['id'] === $selectedId) {
        $product = $item;
        break;
    }
}
$pageTitle = $product['title'];
$pageDescription = $product['description'];
require __DIR__ . '/includes/header.php';
?>
<section class="detail-layout">
  <div>
    <div class="detail-media">
      <img src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
    </div>
    <div class="detail-support">
      <div class="detail-note">
        <span>Condition note</span>
        <strong>Seller-facing copy already structured for later validation and moderation.</strong>
      </div>
      <div class="detail-note">
        <span>Collection route</span>
        <strong>Designed for collection first, with local delivery available in the MVP.</strong>
      </div>
      <div class="detail-note">
        <span>Buyer trust</span>
        <strong>Ratings, seller status, and admin moderation will map cleanly to MySQL data later.</strong>
      </div>
    </div>
  </div>
  <div class="detail-copy detail-panel">
    <p class="eyebrow"><?php echo htmlspecialchars($product['category']); ?></p>
    <h1><?php echo htmlspecialchars($product['title']); ?></h1>
    <p class="detail-price"><?php echo htmlspecialchars($product['price']); ?></p>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <div class="seller-card">
      <div>
        <strong><?php echo htmlspecialchars($product['seller']); ?></strong>
        <span><?php echo htmlspecialchars($product['location']); ?> · Rating <?php echo htmlspecialchars($product['rating']); ?></span>
      </div>
      <span class="badge"><?php echo htmlspecialchars($product['status']); ?></span>
    </div>
    <div class="hero-actions">
      <a class="button button-dark" href="<?php echo htmlspecialchars(app_url('checkout.php')); ?>">Start order</a>
      <a class="button button-light" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Back to listings</a>
    </div>
    <div class="detail-story">
      <div class="detail-story-item"><span>Fulfilment</span><strong>Collection or local delivery</strong></div>
      <div class="detail-story-item"><span>Payment</span><strong>EFT, cash on collection, or mock card</strong></div>
      <div class="detail-story-item"><span>Admin flow</span><strong>Moderation and seller verification planned next</strong></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
