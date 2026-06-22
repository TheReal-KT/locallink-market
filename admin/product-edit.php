<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_product_rows(array $filters): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $rows = array_map(static function (array $row): array {
            $product = market_map_product($row);
            return [
                'id' => (int) $product['id'],
                'category_id' => (int) $product['category_id'],
                'category' => (string) $product['category'],
                'title' => (string) $product['title'],
                'description' => (string) $product['description'],
                'price_amount' => (float) $product['price_amount'],
                'price' => (string) $product['price'],
                'stock' => (int) $product['stock'],
                'status' => 'active',
                'image' => (string) $product['image'],
                'created_at' => (string) $product['created_at'],
            ];
        }, market_sample_products());
    } else {
        $statusSelect = market_table_has_column('products', 'status') ? 'p.status' : "'active' AS status";
        $imageJoin = market_has_product_images() ? 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1' : '';
        $imageSelect = market_has_product_images() ? 'COALESCE(pi.image_path, p.image_path) AS resolved_image_path' : 'p.image_path AS resolved_image_path';
        $statement = $pdo->query('SELECT p.id, p.category_id, p.title, p.description, p.price, p.stock, p.image_path, p.created_at, ' . $statusSelect . ', ' . $imageSelect . ', c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ' . $imageJoin . ' ORDER BY p.created_at DESC, p.id DESC');
        $rows = array_map(static function (array $row): array {
            $product = market_map_product($row);
            return [
                'id' => (int) $product['id'],
                'category_id' => (int) $product['category_id'],
                'category' => (string) $product['category'],
                'title' => (string) $product['title'],
                'description' => (string) $product['description'],
                'price_amount' => (float) $product['price_amount'],
                'price' => (string) $product['price'],
                'stock' => (int) $product['stock'],
                'status' => strtolower((string) ($row['status'] ?? 'active')),
                'image' => (string) $product['image'],
                'created_at' => (string) $product['created_at'],
            ];
        }, $statement->fetchAll());
    }

    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $categoryId = max(0, (int) ($filters['category_id'] ?? 0));
    $status = trim((string) ($filters['status'] ?? 'all'));

    return array_values(array_filter($rows, static function (array $row) use ($search, $categoryId, $status): bool {
        if ($categoryId > 0 && (int) $row['category_id'] !== $categoryId) {
            return false;
        }
        if ($status !== 'all' && $row['status'] !== $status) {
            return false;
        }
        if ($search === '') {
            return true;
        }
        return strpos(strtolower($row['title'] . ' ' . $row['description'] . ' ' . $row['category']), $search) !== false;
    }));
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    try {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price = trim((string) ($_POST['price'] ?? ''));
        $stock = trim((string) ($_POST['stock'] ?? '0'));
        $status = (string) ($_POST['status'] ?? 'active');
        if ($categoryId < 1 || $title === '' || $description === '') {
            throw new RuntimeException('Complete the product form before saving.');
        }
        if (!is_numeric($price) || (float) $price <= 0) {
            throw new RuntimeException('Enter a valid product price.');
        }
        if (!ctype_digit($stock)) {
            throw new RuntimeException('Stock must be zero or more.');
        }
        if (!in_array($status, ['active', 'archived'], true)) {
            throw new RuntimeException('Choose a valid product status.');
        }
        $pdo = db_try_get_connection();
        if (!$pdo) {
            throw new RuntimeException(market_database_unavailable_message());
        }
        $fields = ['category_id = :category_id', 'title = :title', 'description = :description', 'price = :price', 'stock = :stock'];
        $params = ['product_id' => $productId, 'category_id' => $categoryId, 'title' => $title, 'description' => $description, 'price' => (float) $price, 'stock' => (int) $stock];
        if (market_table_has_column('products', 'status')) {
            $fields[] = 'status = :status';
            $params['status'] = $status;
        }
        $statement = $pdo->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :product_id');
        $statement->execute($params);
        app_set_flash('success', 'Product updated.');
        admin_redirect('product-edit.php?id=' . $productId);
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
        admin_redirect('product-edit.php?id=' . (int) ($_POST['product_id'] ?? 0));
    }
}

$filters = ['search' => trim((string) ($_GET['search'] ?? '')), 'category_id' => max(0, (int) ($_GET['category_id'] ?? 0)), 'status' => trim((string) ($_GET['status'] ?? 'all'))];
$categories = market_get_categories();
$products = admin_product_rows($filters);
$selectedId = max(0, (int) ($_GET['id'] ?? 0));
$selectedProduct = null;
foreach ($products as $product) {
    if ((int) $product['id'] === $selectedId) {
        $selectedProduct = $product;
        break;
    }
}
if ($selectedProduct === null && $products !== []) {
    $selectedProduct = $products[0];
}
$notices = admin_collect_notices();
$pageTitle = 'Admin Products';
$pageDescription = 'Edit product details, stock, and moderation status.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('product-edit'); ?>
  <div class="stack">
    <div class="section-head"><div><p class="eyebrow">Product moderation</p><h1>Catalog editor</h1></div><a class="button button-secondary" href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Create new product</a></div>
    <?php admin_render_notices($notices); ?>
    <form class="card" method="get" action="<?php echo htmlspecialchars(app_url('admin/product-edit.php')); ?>">
      <div class="field-row">
        <div class="field"><label for="product-search">Search</label><input id="product-search" name="search" type="search" placeholder="Product title" value="<?php echo htmlspecialchars($filters['search']); ?>"></div>
        <div class="field"><label for="product-category">Category</label><select id="product-category" name="category_id"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category['id']; ?>" <?php echo $filters['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
      </div>
      <div class="field-row">
        <div class="field"><label for="product-status">Status</label><select id="product-status" name="status"><option value="all">All statuses</option><option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="archived" <?php echo $filters['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option></select></div>
        <div class="form-actions"><button class="button" type="submit">Apply filters</button><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/product-edit.php')); ?>">Clear</a></div>
      </div>
    </form>
    <?php if ($selectedProduct !== null): ?>
      <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('admin/product-edit.php')); ?>">
        <p class="eyebrow">Selected product</p>
        <input type="hidden" name="product_id" value="<?php echo (int) $selectedProduct['id']; ?>">
        <div class="field-row">
          <div class="field"><label for="selected-title">Title</label><input id="selected-title" name="title" type="text" value="<?php echo htmlspecialchars($selectedProduct['title']); ?>"></div>
          <div class="field"><label for="selected-category">Category</label><select id="selected-category" name="category_id"><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category['id']; ?>" <?php echo $selectedProduct['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="selected-price">Price</label><input id="selected-price" name="price" type="number" min="0.01" step="0.01" value="<?php echo htmlspecialchars(number_format((float) $selectedProduct['price_amount'], 2, '.', '')); ?>"></div>
          <div class="field"><label for="selected-stock">Stock</label><input id="selected-stock" name="stock" type="number" min="0" step="1" value="<?php echo (int) $selectedProduct['stock']; ?>"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="selected-status">Status</label><select id="selected-status" name="status"><option value="active" <?php echo $selectedProduct['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="archived" <?php echo $selectedProduct['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option></select></div>
          <div class="card subtle-card stack"><span class="eyebrow">Current image</span><img src="<?php echo htmlspecialchars(app_url($selectedProduct['image'])); ?>" alt="<?php echo htmlspecialchars($selectedProduct['title']); ?>" style="width:100%;max-width:220px;border:var(--ll-line);background:var(--ll-photo);"></div>
        </div>
        <div class="field"><label for="selected-description">Description</label><textarea id="selected-description" name="description" rows="5"><?php echo htmlspecialchars($selectedProduct['description']); ?></textarea></div>
        <button class="button" type="submit">Save product</button>
      </form>
    <?php else: ?>
      <div class="card empty-state">Choose a product from the table below to edit it.</div>
    <?php endif; ?>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">Catalog list</p><h2><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?></h2></div></div>
      <table>
        <thead><tr><th>Product</th><th>Category</th><th>Status</th><th>Stock</th><th>Price</th><th>Created</th><th>Open</th></tr></thead>
        <tbody>
          <?php if ($products === []): ?>
            <tr><td colspan="7">No products matched the current filters.</td></tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['title']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars(admin_humanize($product['status'])); ?></span></td>
                <td><?php echo (int) $product['stock']; ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($product['created_at'])); ?></td>
                <td><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/product-edit.php?id=' . $product['id'])); ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
