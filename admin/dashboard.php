<?php
require dirname(__DIR__) . '/includes/data.php';
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Admin moderation and RBAC overview.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="dashboard-layout">
  <aside class="dashboard-sidebar">
    <strong>Admin workspace</strong>
    <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Dashboard</a>
    <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Users</a>
    <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Roles</a>
    <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Verification</a>
    <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Moderation</a>
  </aside>
  <div class="dashboard-main">
    <div class="section-heading">
      <p class="eyebrow">Admin prototype</p>
      <h1>Moderate users, sellers, products, and reviews.</h1>
    </div>
    <div class="stat-grid">
      <div class="stat-card"><span>Pending sellers</span><strong>7</strong></div>
      <div class="stat-card"><span>Flagged products</span><strong>3</strong></div>
      <div class="stat-card"><span>Open reviews</span><strong>11</strong></div>
    </div>
    <div class="dashboard-callout">
      <span>RBAC preview</span>
      <p>The admin prototype demonstrates layout and moderation intent only. Real role enforcement, user CRUD, logs, and approval actions still need implementation.</p>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>User</th><th>Role</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          <tr><td>Thabo N.</td><td>Seller</td><td><span class="badge">Verification pending</span></td><td>Review</td></tr>
          <tr><td>Lebo M.</td><td>Seller</td><td><span class="badge">Approved</span></td><td>Edit role</td></tr>
          <tr><td>Nandi P.</td><td>Buyer</td><td><span class="badge">Active</span></td><td>Suspend</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
