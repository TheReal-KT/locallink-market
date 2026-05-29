<?php
require __DIR__ . '/includes/data.php';
$pageTitle = 'Products';
$pageDescription = 'Browse LocalLink product listings with search and filters.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <p class="eyebrow">Product listing</p>
  <h1>Browse trusted local listings.</h1>
  <p>Search by product name, filter by category, and compare seller signals before you order.</p>
</section>

<section class="catalog-layout">
  <aside class="filter-panel">
    <form class="filter-form">
      <label for="search">Search</label>
      <input id="search" type="search" placeholder="Phone, backpack, textbook">
      <label for="category">Category</label>
      <select id="category">
        <option>All categories</option>
        <?php foreach ($categories as $category): ?>
          <option><?php echo htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <label for="price">Max price</label>
      <input id="price" type="range" min="100" max="3000" value="1500">
      <button class="button button-dark" type="button">Apply filters</button>
    </form>
  </aside>
  <div>
    <div class="toolbar">
      <span><?php echo count($products); ?> listings</span>
      <select aria-label="Sort products">
        <option>Newest first</option>
        <option>Lowest price</option>
        <option>Highest rating</option>
      </select>
    </div>
    <div class="product-grid catalog-grid">
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
            <span class="badge"><?php echo htmlspecialchars($product['status']); ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
