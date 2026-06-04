<?php
require __DIR__ . '/includes/app.php';
$search = trim((string) ($_GET['search'] ?? ''));
$selectedCategoryId = max(0, (int) ($_GET['category'] ?? 0));
$selectedSort = (string) ($_GET['sort'] ?? 'newest');
$categories = market_get_categories();
$products = market_get_products([
    'search' => $search,
    'category_id' => $selectedCategoryId,
    'sort' => $selectedSort,
]);
$pageTitle = 'Products';
$pageDescription = 'Browse the product catalog with simple search and category filters.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section">
  <div class="section-head">
    <div>
      <p class="eyebrow">Catalog</p>
      <h1>Browse products</h1>
    </div>
    <p>Use the simplified filters to find products quickly without extra catalog logic.</p>
  </div>
  <form class="card filter-form" method="get" action="<?php echo htmlspecialchars(app_url('products.php')); ?>">
    <div class="field">
      <label for="search">Search</label>
      <input id="search" name="search" type="search" placeholder="Phone, backpack, textbook" value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="field">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="0">All categories</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?php echo (int) $category['id']; ?>" <?php echo $selectedCategoryId === (int) $category['id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($category['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="sort">Sort</label>
      <select id="sort" name="sort">
        <option value="newest" <?php echo $selectedSort === 'newest' ? 'selected' : ''; ?>>Newest first</option>
        <option value="price_low" <?php echo $selectedSort === 'price_low' ? 'selected' : ''; ?>>Lowest price</option>
      </select>
    </div>
    <div class="form-actions">
      <button class="button" type="submit">Apply filters</button>
      <a class="text-link" href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Clear</a>
    </div>
  </form>
</section>

<section class="page section">
  <div class="section-head">
    <div>
      <p class="eyebrow">Results</p>
      <h2><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?></h2>
    </div>
    <p>Products load from MySQL when the database is available, with sample data as a fallback.</p>
  </div>
  <?php if ($products === []): ?>
    <div class="card empty-state">No products match the current filters. Try a broader search or switch categories.</div>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
