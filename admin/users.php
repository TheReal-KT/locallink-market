<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_users_page_rows(array $filters): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $rows = array_map(static function (array $row): array {
            $row = market_normalize_user($row);
            return [
                'id' => (int) $row['id'],
                'full_name' => (string) $row['full_name'],
                'email' => (string) $row['email'],
                'role' => (string) $row['role'],
                'status' => (string) $row['status'],
                'created_at' => (string) $row['created_at'],
                'last_login_at' => null,
            ];
        }, market_sample_users());
    } else {
        $lastLoginSelect = market_users_have_column('last_login_at') ? 'last_login_at' : 'NULL AS last_login_at';
        $statement = $pdo->query('SELECT ' . market_user_select_columns() . ', ' . $lastLoginSelect . ' FROM users ORDER BY created_at DESC');
        $rows = array_map(static function (array $row): array {
            $row = market_normalize_user($row);
            return [
                'id' => (int) $row['id'],
                'full_name' => (string) $row['full_name'],
                'email' => (string) $row['email'],
                'role' => (string) $row['role'],
                'status' => (string) $row['status'],
                'created_at' => (string) $row['created_at'],
                'last_login_at' => isset($row['last_login_at']) ? (string) $row['last_login_at'] : null,
            ];
        }, $statement->fetchAll());
    }

    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $role = strtolower(trim((string) ($filters['role'] ?? 'all')));
    $status = strtolower(trim((string) ($filters['status'] ?? 'all')));

    return array_values(array_filter($rows, static function (array $row) use ($search, $role, $status): bool {
        if ($role !== 'all' && $row['role'] !== $role) {
            return false;
        }
        if ($status !== 'all' && $row['status'] !== $status) {
            return false;
        }
        if ($search === '') {
            return true;
        }

        $haystack = strtolower($row['full_name'] . ' ' . $row['email']);
        return strpos($haystack, $search) !== false;
    }));
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    try {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = (string) ($_POST['role'] ?? 'buyer');
        $status = (string) ($_POST['status'] ?? 'active');
        if (!in_array($role, ['buyer', 'admin'], true)) {
            throw new RuntimeException('Choose a valid role.');
        }
        if (!in_array($status, ['active', 'disabled'], true)) {
            throw new RuntimeException('Choose a valid status.');
        }
        $pdo = db_try_get_connection();
        if (!$pdo) {
            throw new RuntimeException(market_database_unavailable_message());
        }
        $fields = ['is_admin = :is_admin'];
        $params = ['user_id' => $userId, 'is_admin' => $role === 'admin' ? 1 : 0];
        if (market_users_have_column('role')) {
            $fields[] = 'role = :role';
            $params['role'] = $role;
        }
        if (market_users_have_column('status')) {
            $fields[] = 'status = :status';
            $params['status'] = $status;
        }
        $statement = $pdo->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :user_id');
        $statement->execute($params);
        app_set_flash('success', 'User access updated.');
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
    }

    admin_redirect('users.php');
}

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'role' => trim((string) ($_GET['role'] ?? 'all')),
    'status' => trim((string) ($_GET['status'] ?? 'all')),
];
$users = admin_users_page_rows($filters);
$notices = admin_collect_notices();
$pageTitle = 'Admin Users';
$pageDescription = 'Manage roles and access status for LocalLink accounts.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('users'); ?>
  <div class="stack">
    <div class="section-head">
      <div><p class="eyebrow">User management</p><h1>Accounts and access</h1></div>
      <p>Disabled accounts cannot sign in, and the current role model remains Buyer/Admin only.</p>
    </div>
    <?php admin_render_notices($notices); ?>
    <form class="card" method="get" action="<?php echo htmlspecialchars(app_url('admin/users.php')); ?>">
      <div class="field-row">
        <div class="field"><label for="user-search">Search</label><input id="user-search" name="search" type="search" placeholder="Name or email" value="<?php echo htmlspecialchars($filters['search']); ?>"></div>
        <div class="field"><label for="user-role">Role</label><select id="user-role" name="role"><option value="all">All roles</option><option value="buyer" <?php echo $filters['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option><option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option></select></div>
      </div>
      <div class="field-row">
        <div class="field"><label for="user-status">Status</label><select id="user-status" name="status"><option value="all">All statuses</option><option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="disabled" <?php echo $filters['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option></select></div>
        <div class="form-actions"><button class="button" type="submit">Apply filters</button><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/users.php')); ?>">Clear</a></div>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">User list</p><h2><?php echo count($users); ?> account<?php echo count($users) === 1 ? '' : 's'; ?></h2></div></div>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Last login</th><th>Access</th></tr></thead>
        <tbody>
          <?php if ($users === []): ?>
            <tr><td colspan="5">No users matched the current filters.</td></tr>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br><span class="hint">#<?php echo (int) $user['id']; ?></span></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($user['created_at'])); ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($user['last_login_at'])); ?></td>
                <td>
                  <form class="stack" method="post" action="<?php echo htmlspecialchars(app_url('admin/users.php')); ?>">
                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                    <div class="field"><label>Role</label><select name="role"><option value="buyer" <?php echo $user['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option><option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option></select></div>
                    <div class="field"><label>Status</label><select name="status"><option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="disabled" <?php echo $user['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option></select></div>
                    <button class="button" type="submit">Save</button>
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
