<?php
$pageTitle = 'Login';
$pageDescription = 'Sign in to LocalLink Market.';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-copy">
    <p class="eyebrow">Welcome back</p>
    <h1>Sign in to manage orders, listings, and moderation tasks.</h1>
    <p>Use this prototype screen for buyer, seller, and admin login evidence, with a stronger editorial layout that aligns to the Wonder direction.</p>
    <div class="support-grid">
      <div class="support-card">
        <span>Order tracking</span>
        <strong>Buyers return here to follow pending and completed orders.</strong>
      </div>
      <div class="support-card">
        <span>Seller messages</span>
        <strong>Sellers will eventually review listings, requests, and product status from one account area.</strong>
      </div>
      <div class="support-card">
        <span>Quick support</span>
        <strong>Admin access, review moderation, and verification workflows can sit behind the same auth shell.</strong>
      </div>
    </div>
  </div>
  <form class="auth-form">
    <p class="eyebrow">Account access</p>
    <label for="email">Email address</label>
    <input id="email" type="email" placeholder="you@example.com">
    <label for="password">Password</label>
    <input id="password" type="password" placeholder="Enter password">
    <button class="button button-dark" type="button">Sign in</button>
    <a class="text-link" href="/register.php">Create a new account</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
