<?php
$pageTitle = $pageTitle ?? 'LocalLink Market';
$pageDescription = $pageDescription ?? 'A responsive C2C marketplace prototype.';
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
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <header class="site-header">
    <a class="brand" href="/index.php" aria-label="LocalLink Market home">
      <span class="brand-mark">LL</span>
      <span>LocalLink</span>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <nav id="primary-nav" class="primary-nav" aria-label="Primary navigation">
      <a href="/products.php">Browse</a>
      <a href="/buyer-dashboard.php">Buyer</a>
      <a href="/seller-dashboard.php">Seller</a>
      <a href="/admin/dashboard.php">Admin</a>
      <a class="nav-action" href="/login.php">Sign in</a>
    </nav>
  </header>
  <main>
