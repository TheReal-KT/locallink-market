<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_category_rows(): array
{
    $pdo = db_try_get_connection();
    if (!$pdo) {
        return array_map(static function (array $row): array {
            return ['id' => (int) $row['id'], 'name' => (string) $row['name'], 'count' => (int) $row['count'], 'latest_product_at' => null];
        }, market_sample_categories());
    }

    $statement = $pdo->query('SELECT c.id, c.name, COUNT(p.id) AS item_count, MAX(p.created_at) AS latest_product_at FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id, c.name ORDER BY c.name ASC');
    return array_map(static function (array $row): array {
        return ['id' => (int) $row['id'], 'name' => (string) $row['name'], 'count' => (int) $row['item_count'], 'latest_product_at' => $row['latest_product_at'] ?? null];
    }, $statement->fetchAll());
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    $action = (string) ($_POST['action'] ?? '');

    try {
        $pdo = db_try_get_connection();
        if (!$pdo) {
            throw new RuntimeException(market_database_unavailable_message());
        }

        if ($action === 'create') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Enter a category name.');
            }
            $statement = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
            $statement->execute(['name' => $name]);
            app_set_flash('success', 'Category added.');
        } elseif ($action === 'update') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Enter a category name.');
            }
            $statement = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :category_id');
            $statement->execute(['category_id' => (int) ($_POST['category_id'] ?? 0), 'name' => $name]);
            app_set_flash('success', 'Category updated.');
        } elseif ($action === 'delete') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :category_id');
            $count->execute(['category_id' => $categoryId]);
            if ((int) $count->fetchColumn() > 0) {
                throw new RuntimeException('Move or archive products in that category before deleting it.');
            }
            $statement = $pdo->prepare('DELETE FROM categories WHERE id = :category_id');
            $statement->execute(['category_id' => $categoryId]);
            app_set_flash('success', 'Category removed.');
        }
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
    }

    admin_redirect('categories.php');
}

$categories = admin_category_rows();
$notices = admin_collect_notices();
$pageTitle = 'Admin Categories';
$pageDescription = 'Create, rename, and clean up product categories.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('categories'); ?>
  <div class="stack">
    <div class="section-head">
      <div><p class="eyebrow">Category management</p><h1>Catalog structure</h1></div>
      <p>Only empty categories can be deleted from this page. Move or archive products first when a category is still in use.</p>
    </div>
    <?php admin_render_notices($notices); ?>
    <div class="split">
      <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('admin/categories.php')); ?>">
        <p class="eyebrow">Add category</p>
        <input type="hidden" name="action" value="create">
        <div class="field"><label for="category-name">Category name</label><input id="category-name" name="name" type="text" placeholder="New category name"></div>
        <button class="button" type="submit">Create category</button>
      </form>
      <div class="card stack">
        <p class="eyebrow">Current scope</p>
        <h2>Keep the catalog tidy</h2>
        <div class="feature-list">
          <div>Create new browsing groups</div>
          <div>Rename categories without touching product forms</div>
          <div>Safely delete only empty categories</div>
        </div>
      </div>
    </div>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">Category list</p><h2><?php echo count($categories); ?> categor<?php echo count($categories) === 1 ? 'y' : 'ies'; ?></h2></div></div>
      <table>
        <thead><tr><th>Name</th><th>Products</th><th>Latest product</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($categories === []): ?>
            <tr><td colspan="4">No categories are available yet.</td></tr>
          <?php else: ?>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td><?php echo (int) $category['count']; ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($category['latest_product_at'])); ?></td>
                <td>
                  <form class="stack" method="post" action="<?php echo htmlspecialchars(app_url('admin/categories.php')); ?>">
                    <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                    <div class="field"><label>Rename</label><input name="name" type="text" value="<?php echo htmlspecialchars($category['name']); ?>"></div>
                    <div class="form-actions">
                      <button class="button" type="submit" name="action" value="update">Save</button>
                      <?php if ((int) $category['count'] === 0): ?>
                        <button class="button button-secondary" type="submit" name="action" value="delete">Delete</button>
                      <?php endif; ?>
                    </div>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
