<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Home';
$pageDescription = 'Discover nearby sellers and trusted second-hand goods.';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow eyebrow-accent">Buy from nearby sellers</p>
    <h1>Find local deals from people near you.</h1>
    <p class="hero-text">LocalLink Market helps you browse everyday items, compare trusted sellers, and place orders with less back-and-forth.</p>
    <div class="hero-actions">
      <a class="button button-dark" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
      <a class="button button-light" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">List an item</a>
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
        <div class="shortcut-row"><span>Price range</span><strong>R 200 to R 2 500</strong></div>
      </div>
      <div class="market-pulse">
        <span>Today in Gauteng</span>
        <p>124 active listings, fresh products added this week, and strong activity on the most viewed items.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-heading">
    <p class="eyebrow">Categories</p>
    <h2>Shop by what you need today.</h2>
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
    <p class="eyebrow">How it works</p>
    <h2>Simple steps for buying and selling locally.</h2>
  </div>
  <div class="flow-list">
    <div><strong>1</strong><span>Sellers add clear product details, prices, and collection options.</span></div>
    <div><strong>2</strong><span>Buyers compare nearby listings and choose the option that fits.</span></div>
    <div><strong>3</strong><span>Orders, delivery notes, and account activity stay easy to follow.</span></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
