<?php
require_once __DIR__ . '/app.php';

function admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => 'Overview', 'path' => 'admin/dashboard.php'],
        'users' => ['label' => 'Users', 'path' => 'admin/users.php'],
        'categories' => ['label' => 'Categories', 'path' => 'admin/categories.php'],
        'orders' => ['label' => 'Orders', 'path' => 'admin/orders.php'],
        'reviews' => ['label' => 'Reviews', 'path' => 'admin/reviews.php'],
        'verification' => ['label' => 'Verification', 'path' => 'admin/verification.php'],
        'product-edit' => ['label' => 'Products', 'path' => 'admin/product-edit.php'],
    ];
}

function admin_render_sidebar(string $activeKey): void
{
    $items = admin_nav_items();
    ?>
    <aside class="card sidebar">
      <strong>Admin tools</strong>
      <?php foreach ($items as $key => $item): ?>
        <a class="<?php echo $key === $activeKey ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['path'])); ?>"><?php echo htmlspecialchars($item['label']); ?></a>
      <?php endforeach; ?>
      <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
    </aside>
    <?php
}

function admin_collect_notices(): array
{
    return [
        'success' => app_pull_flash('success'),
        'error' => app_pull_flash('error'),
    ];
}

function admin_render_notices(array $notices): void
{
    if (!empty($notices['success'])) {
        echo '<div class="notice notice-success">' . htmlspecialchars((string) $notices['success']) . '</div>';
    }

    if (!empty($notices['error'])) {
        echo '<div class="notice notice-error">' . htmlspecialchars((string) $notices['error']) . '</div>';
    }
}

function admin_redirect(string $path): void
{
    app_redirect('admin/' . ltrim($path, '/'));
}

function admin_format_datetime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('j M Y H:i', $timestamp) : '-';
}

function admin_humanize(string $value): string
{
    return ucwords(str_replace('_', ' ', trim($value)));
}

function admin_table_from_candidates(array $candidates): ?string
{
    foreach ($candidates as $table) {
        if (market_table_exists($table)) {
            return $table;
        }
    }

    return null;
}

function admin_demo_rows(string $key, array $rows): array
{
    $overrides = $_SESSION['admin_demo'][$key] ?? [];

    foreach ($rows as &$row) {
        $id = (int) ($row['id'] ?? 0);
        if ($id > 0 && isset($overrides[$id]) && is_array($overrides[$id])) {
            $row = array_merge($row, $overrides[$id]);
        }
    }
    unset($row);

    return $rows;
}

function admin_set_demo_row_override(string $key, int $id, array $values): void
{
    if (!isset($_SESSION['admin_demo'][$key]) || !is_array($_SESSION['admin_demo'][$key])) {
        $_SESSION['admin_demo'][$key] = [];
    }

    $current = $_SESSION['admin_demo'][$key][$id] ?? [];
    $_SESSION['admin_demo'][$key][$id] = array_merge(is_array($current) ? $current : [], $values);
}
