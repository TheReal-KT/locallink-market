<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Home';
$pageDescription = 'Discover nearby sellers and trusted second-hand goods.';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow">C2C marketplace for local trade</p>
    <h1>Buy and sell goods with people near you.</h1>
    <p class="hero-text">LocalLink keeps the marketplace direct, readable, and practical for buyers, sellers, and admins who need a low-data responsive experience.</p>
    <div class="hero-actions">
      <a class="button button-dark" href="/products.php">Browse products</a>
      <a class="button button-light" href="/add-product.php">List an item</a>
    </div>
  </div>
  <div class="hero-panel" aria-label="Featured product summary">
    <img src="/assets/images/product-phone.svg" alt="Refurbished smartphone" class="hero-product">
    <div class="hero-card">
      <span class="badge">Verified seller</span>
      <h2>Refurbished smartphone</h2>
      <p>R 2 450 · Mamelodi</p>
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
      <a class="category-card" href="/products.php">
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
    <a class="text-link" href="/products.php">View all</a>
  </div>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <article class="product-card">
        <a href="/product.php?id=<?php echo $product['id']; ?>" class="product-media">
          <img src="/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
        </a>
        <div class="product-body">
          <div class="product-meta">
            <span><?php echo htmlspecialchars($product['category']); ?></span>
            <span><?php echo htmlspecialchars($product['location']); ?></span>
          </div>
          <h3><a href="/product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['title']); ?></a></h3>
          <p><?php echo htmlspecialchars($product['price']); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section split-section">
  <div>
    <p class="eyebrow">Trust workflow</p>
    <h2>Buyer, seller, and admin flows fit into one simple system.</h2>
  </div>
  <div class="flow-list">
    <div><strong>1</strong><span>Seller lists a product and requests verification.</span></div>
    <div><strong>2</strong><span>Buyer searches, compares seller details, and places an order.</span></div>
    <div><strong>3</strong><span>Admin moderates users, products, reviews, and roles.</span></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
