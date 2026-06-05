<?php
require __DIR__ . '/includes/app.php';
$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$product = market_get_product_by_id($selectedId);

if ($product === null) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    $pageDescription = 'The product you requested is no longer available.';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page section">
      <div class="card stack">
        <p class="eyebrow">Listing unavailable</p>
        <h1>This product could not be found.</h1>
        <p>The link may be outdated or the item may have been removed.</p>
        <a class="button" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    return;
}

$pageTitle = $product['title'];
$pageDescription = $product['description'];
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card media-card">
    <div class="product-detail-media">
      <img class="product-photo product-photo-<?php echo (int) $product['category_id']; ?>" src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
    </div>
  </div>
  <div class="card stack">
    <p class="eyebrow"><?php echo htmlspecialchars($product['category']); ?></p>
    <h1><?php echo htmlspecialchars($product['title']); ?></h1>
    <p class="price price-large"><?php echo htmlspecialchars($product['price']); ?></p>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <div class="detail-list">
      <div class="info-row"><strong>Category</strong><span><?php echo htmlspecialchars($product['category']); ?></span></div>
      <div class="info-row"><strong>Stock</strong><span><?php echo htmlspecialchars($product['stock_label']); ?></span></div>
      <div class="info-row"><strong>Status</strong><span><?php echo $product['stock'] > 0 ? 'Available to order' : 'Unavailable'; ?></span></div>
    </div>
    <div class="cta-row">
      <a class="button" href="<?php echo htmlspecialchars(app_url('checkout.php?product_id=' . $product['id'])); ?>">Buy now</a>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Back to products</a>
    </div>
    <div class="feature-list">
      <div>Delivery options are chosen during checkout.</div>
      <div>Payment can be EFT, cash, or card.</div>
      <div>Orders appear on the customer dashboard after purchase.</div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
