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
  <div class="detail-media">
    <img src="/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
  </div>
  <div class="detail-copy">
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
      <a class="button button-dark" href="/checkout.php">Start order</a>
      <a class="button button-light" href="/products.php">Back to listings</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
