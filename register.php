<?php
$pageTitle = 'Register';
$pageDescription = 'Create a buyer or seller account.';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-copy">
    <p class="eyebrow">Join LocalLink</p>
    <h1>Create a buyer account or request seller access.</h1>
    <p>The form is structured for later PHP validation and RBAC role assignment.</p>
  </div>
  <form class="auth-form">
    <label for="name">Full name</label>
    <input id="name" type="text" placeholder="Full name">
    <label for="register-email">Email address</label>
    <input id="register-email" type="email" placeholder="you@example.com">
    <label for="role">Account type</label>
    <select id="role">
      <option>Buyer</option>
      <option>Seller request</option>
    </select>
    <label for="register-password">Password</label>
    <input id="register-password" type="password" placeholder="Create password">
    <button class="button button-dark" type="button">Create account</button>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
