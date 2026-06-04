<?php
require __DIR__ . '/includes/app.php';
$categories = market_get_categories();
$products = market_get_products(['limit' => 4, 'sort' => 'newest']);
$pageTitle = 'Home';
$pageDescription = 'Simple ecommerce storefront for browsing products and placing orders.';
require __DIR__ . '/includes/header.php';
?>
<section class="page hero split">
  <div class="stack">
    <p class="eyebrow">Simple ecommerce store</p>
    <h1>Browse products, sign in, and manage the store from one small project.</h1>
    <p>This version keeps the project focused on the essentials: product pages, customer accounts, checkout, and an admin dashboard.</p>
    <div class="cta-row">
      <a class="button" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create account</a>
    </div>
  </div>
  <div class="card stack">
    <h2>Project scope</h2>
    <div class="feature-list">
      <div>Product listing and detail pages</div>
      <div>Email and password login for customers</div>
      <div>Checkout with order history</div>
      <div>Admin dashboard with product management</div>
    </div>
    <div class="mini-stats">
      <div><strong><?php echo count($categories); ?></strong><span>Categories</span></div>
      <div><strong><?php echo count($products); ?></strong><span>Featured products</span></div>
      <div><strong>2</strong><span>Demo roles</span></div>
    </div>
  </div>
</section>

<section class="page section">
  <div class="section-head">
    <div>
      <p class="eyebrow">Categories</p>
      <h2>Shop by category</h2>
    </div>
    <p>Keep the homepage lightweight and point users directly to the catalog.</p>
  </div>
  <div class="grid grid-4">
    <?php foreach ($categories as $category): ?>
      <a class="card category-card" href="<?php echo htmlspecialchars(app_url('products.php?category=' . $category['id'])); ?>">
        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
        <span><?php echo (int) $category['count']; ?> items</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="page section">
  <div class="section-head">
    <div>
      <p class="eyebrow">Latest products</p>
      <h2>Recent additions to the store</h2>
    </div>
    <a class="text-link" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">View all products</a>
  </div>
  <?php if ($products === []): ?>
    <div class="card empty-state">No products are available yet. Add products from the admin dashboard after the database is connected.</div>
  <?php else: ?>
    <div class="grid product-grid">
      <?php foreach ($products as $product): ?>
        <article class="card product-card">
          <a class="product-media" href="<?php echo htmlspecialchars(app_url('product.php?id=' . $product['id'])); ?>">
            <img src="<?php echo htmlspecialchars(app_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
          </a>
          <div class="product-body">
            <p class="eyebrow"><?php echo htmlspecialchars($product['category']); ?></p>
            <h3><a href="<?php echo htmlspecialchars(app_url('product.php?id=' . $product['id'])); ?>"><?php echo htmlspecialchars($product['title']); ?></a></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <div class="product-meta">
              <strong class="price"><?php echo htmlspecialchars($product['price']); ?></strong>
              <span class="badge"><?php echo htmlspecialchars($product['stock_label']); ?></span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="page section">
  <div class="card split info-card">
    <div class="stack">
      <p class="eyebrow">Why this version</p>
      <h2>Less code, clearer focus.</h2>
      <p>The older seller workflow, moderation queue, and extra data model were removed so the project stays aligned with the store, login, and admin requirements.</p>
    </div>
    <div class="stack">
      <div class="info-row"><strong>Storefront</strong><span>Home, catalog, product detail</span></div>
      <div class="info-row"><strong>Customer</strong><span>Register, login, checkout, orders</span></div>
      <div class="info-row"><strong>Admin</strong><span>Dashboard, product creation, basic reporting</span></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
