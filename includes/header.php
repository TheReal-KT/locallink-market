<?php
require_once __DIR__ . '/app.php';
$pageTitle = $pageTitle ?? 'LocalLink Market';
$pageDescription = $pageDescription ?? 'Buy and sell trusted local goods with nearby people.';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <title><?php echo htmlspecialchars($pageTitle); ?> | LocalLink Market</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(app_url('assets/css/styles.css')); ?>">
</head>
<body>
  <header class="site-header">
    <div class="brand-cluster">
      <a class="brand" href="<?php echo htmlspecialchars(app_url('index.php')); ?>" aria-label="LocalLink Market home">
        <span class="brand-mark">LL</span>
        <span class="brand-copy">
          <span class="brand-name">LocalLink Market</span>
          <span class="brand-note">Local buying and selling</span>
        </span>
      </a>
      <span class="header-kicker">Shop nearby. Sell with confidence.</span>
    </div>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <nav id="primary-nav" class="primary-nav" aria-label="Primary navigation">
      <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse</a>
      <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Seller</a>
      <a href="<?php echo htmlspecialchars(app_url('buyer-dashboard.php')); ?>">Orders</a>
      <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Admin</a>
      <a class="nav-accent" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create account</a>
      <a class="nav-action" href="<?php echo htmlspecialchars(app_url('login.php')); ?>">Sign in</a>
    </nav>
  </header>
  <main>
