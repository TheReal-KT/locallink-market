<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Seller Dashboard';
$pageDescription = 'Seller listings and order management.';
require __DIR__ . '/includes/header.php';
?>
<section class="dashboard-layout">
  <aside class="dashboard-sidebar">
    <strong>Seller workspace</strong>
    <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Overview</a>
    <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
    <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Orders</a>
    <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Settings</a>
  </aside>
  <div class="dashboard-main">
    <div class="section-heading row-heading">
      <div>
        <p class="eyebrow">Seller dashboard</p>
        <h1>Manage listings and buyer requests.</h1>
      </div>
      <a class="button button-dark" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
    </div>
    <div class="stat-grid">
      <?php foreach ($sellerStats as $stat): ?>
        <div class="stat-card"><span><?php echo htmlspecialchars($stat['label']); ?></span><strong><?php echo htmlspecialchars($stat['value']); ?></strong></div>
      <?php endforeach; ?>
    </div>
    <div class="dashboard-callout">
      <span>Seller tools</span>
      <p>Track active listings, buyer requests, and order details from your seller workspace.</p>
    </div>
    <div class="product-grid dashboard-products">
      <?php foreach (array_slice($products, 0, 3) as $product): ?>
        <article class="product-card compact-card">
          <img src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
          <div>
            <h3><?php echo htmlspecialchars($product['title']); ?></h3>
            <p><?php echo htmlspecialchars($product['price']); ?> · <?php echo htmlspecialchars($product['status']); ?></p>
            <span class="product-seller"><?php echo htmlspecialchars($product['seller']); ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
