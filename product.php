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
        <strong>Clear condition details help you decide before placing an order.</strong>
      </div>
      <div class="detail-note">
        <span>Delivery note</span>
        <strong>Delivery details can be arranged directly between the buyer and seller.</strong>
      </div>
      <div class="detail-note">
        <span>Buyer overview</span>
        <strong>Check the seller location, rating, and item details before you buy.</strong>
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
      <div class="detail-story-item"><span>Delivery</span><strong>Seller-arranged delivery options</strong></div>
      <div class="detail-story-item"><span>Payment</span><strong>EFT, cash, or card payment</strong></div>
      <div class="detail-story-item"><span>Support</span><strong>Marketplace support for listing or order questions</strong></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
