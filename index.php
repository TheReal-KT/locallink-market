<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Home';
$pageDescription = 'Discover nearby sellers and trusted second-hand goods.';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow eyebrow-accent">Buy from nearby sellers</p>
    <h1>Local trade with stronger trust signals and lighter screens.</h1>
    <p class="hero-text">LocalLink helps township traders, home businesses, and nearby buyers discover products quickly, compare seller quality, and move from listing to collection without a heavy interface.</p>
    <div class="hero-actions">
      <a class="button button-dark" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
      <a class="button button-light" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">List an item</a>
    </div>
    <div class="hero-trust">
      <div class="trust-item">
        <span>Verified sellers</span>
        <strong>Clear moderation cues for the marketplace and admin demo.</strong>
      </div>
      <div class="trust-item">
        <span>Local collection</span>
        <strong>Pickup-first flows that fit the MVP payment and delivery model.</strong>
      </div>
      <div class="trust-item">
        <span>Low data journey</span>
        <strong>Compact cards, practical forms, and responsive layouts for reporting.</strong>
      </div>
    </div>
  </div>
  <div class="hero-panel" aria-label="Marketplace search shortcuts">
    <div class="hero-search-panel">
      <p class="eyebrow">Start with search</p>
      <div class="hero-search-field">
        <span>Search products, sellers, or areas</span>
        <strong>Search</strong>
      </div>
      <div class="shortcut-list">
        <div class="shortcut-row"><span>Category</span><strong>Phones and homeware</strong></div>
        <div class="shortcut-row"><span>Area</span><strong>Pretoria East and Midrand</strong></div>
        <div class="shortcut-row"><span>Fulfilment</span><strong>Collection and local delivery</strong></div>
      </div>
      <div class="market-pulse">
        <span>Today in Gauteng</span>
        <p>124 active listings, 19 sellers verified this week, and same-day collection on the most viewed items.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-heading">
    <p class="eyebrow">Categories</p>
    <h2>Start with what people actually need.</h2>
  </div>
  <div class="category-grid">
    <?php foreach ($categories as $category): ?>
      <a class="category-card" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">
        <span><?php echo htmlspecialchars($category['name']); ?></span>
        <strong><?php echo htmlspecialchars($category['count']); ?></strong>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section-compact">
  <div class="section-heading row-heading">
    <div>
      <p class="eyebrow">Recent listings</p>
      <h2>Fresh products from nearby sellers.</h2>
    </div>
    <a class="text-link" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">View all</a>
  </div>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <article class="product-card">
        <a href="<?php echo htmlspecialchars(app_url('product.php?id=' . $product['id'])); ?>" class="product-media">
          <img src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
        </a>
        <div class="product-body">
          <div class="product-meta">
            <span><?php echo htmlspecialchars($product['category']); ?></span>
            <span><?php echo htmlspecialchars($product['location']); ?></span>
          </div>
          <h3><a href="<?php echo htmlspecialchars(app_url('product.php?id=' . $product['id'])); ?>"><?php echo htmlspecialchars($product['title']); ?></a></h3>
          <p class="product-price"><?php echo htmlspecialchars($product['price']); ?></p>
          <div class="product-status-row">
            <span class="product-seller"><?php echo htmlspecialchars($product['seller']); ?></span>
            <span class="badge"><?php echo htmlspecialchars($product['status']); ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section split-section">
  <div>
    <p class="eyebrow">Trust workflow</p>
    <h2>Buyer, seller, and admin flows sit in one practical commerce rhythm.</h2>
  </div>
  <div class="flow-list">
    <div><strong>1</strong><span>Seller lists a product, publishes pickup details, and requests verification.</span></div>
    <div><strong>2</strong><span>Buyer filters by area, checks trust cues, and opens the checkout flow.</span></div>
    <div><strong>3</strong><span>Admin handles verification, moderation, and RBAC evidence for the report.</span></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
