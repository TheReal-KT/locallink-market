<?php
$pageTitle = 'Login';
$pageDescription = 'Sign in to LocalLink Market.';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-copy">
    <p class="eyebrow">Welcome back</p>
    <h1>Sign in to manage your LocalLink account.</h1>
    <p>Access your orders, listings, reviews, and account details from one place.</p>
    <div class="support-grid">
      <div class="support-card">
        <span>Order tracking</span>
        <strong>Buyers return here to follow pending and completed orders.</strong>
      </div>
      <div class="support-card">
        <span>Seller messages</span>
        <strong>Sellers can keep track of listings, buyer requests, and product activity.</strong>
      </div>
      <div class="support-card">
        <span>Quick support</span>
        <strong>Get help with account access, order questions, and marketplace activity.</strong>
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
    <a class="text-link" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create a new account</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
