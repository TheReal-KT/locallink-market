<?php
$pageTitle = 'Login';
$pageDescription = 'Sign in to LocalLink Market.';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-copy">
    <p class="eyebrow">Welcome back</p>
    <h1>Sign in to manage orders, listings, and reviews.</h1>
    <p>Use this prototype screen for buyer, seller, and admin login evidence.</p>
  </div>
  <form class="auth-form">
    <label for="email">Email address</label>
    <input id="email" type="email" placeholder="you@example.com">
    <label for="password">Password</label>
    <input id="password" type="password" placeholder="Enter password">
    <button class="button button-dark" type="button">Sign in</button>
    <a class="text-link" href="/register.php">Create a new account</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
