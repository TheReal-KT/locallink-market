<?php
require_once __DIR__ . '/app.php';
$pageTitle = $pageTitle ?? 'LocalLink Market';
$pageDescription = $pageDescription ?? 'Simple ecommerce store with customer login and admin tools.';
$currentUser = app_current_user();
$dashboardPath = $currentUser ? app_dashboard_path_for_user($currentUser) : 'buyer-dashboard.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <title><?php echo htmlspecialchars($pageTitle); ?> | LocalLink Market</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(app_url('assets/css/styles.css')); ?>">
</head>
<body>
  <header class="site-header">
    <a class="brand" href="<?php echo htmlspecialchars(app_url('index.php')); ?>" aria-label="LocalLink Market home">
      <span class="brand-mark">LL</span>
      <span class="brand-copy">
        <span class="brand-name">LocalLink Market</span>
        <span class="brand-note">Simple college ecommerce project</span>
      </span>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <nav id="primary-nav" class="primary-nav" aria-label="Primary navigation">
      <a href="<?php echo htmlspecialchars(app_url('index.php')); ?>">Home</a>
      <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Products</a>
      <?php if ($currentUser !== null && app_is_admin($currentUser)): ?>
        <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">Add product</a>
        <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Admin</a>
      <?php endif; ?>
      <?php if ($currentUser !== null): ?>
        <a href="<?php echo htmlspecialchars(app_url($dashboardPath)); ?>">Account</a>
        <a class="nav-action" href="<?php echo htmlspecialchars(app_url('logout.php')); ?>">Logout</a>
      <?php else: ?>
        <a href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Register</a>
        <a class="nav-action" href="<?php echo htmlspecialchars(app_url('login.php')); ?>">Login</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>
