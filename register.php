<?php
$pageTitle = 'Register';
$pageDescription = 'Create a buyer or seller account.';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-copy">
    <p class="eyebrow">Join LocalLink</p>
    <h1>Create a buyer account or request seller access.</h1>
    <p>The form is structured for later PHP validation and RBAC role assignment, while keeping the page ready for desktop, tablet, and mobile screenshots.</p>
    <div class="support-grid">
      <div class="support-card">
        <span>Buyer route</span>
        <strong>Create an account, browse local listings, and place an order.</strong>
      </div>
      <div class="support-card">
        <span>Seller route</span>
        <strong>Request seller access, list products, and manage received orders.</strong>
      </div>
      <div class="support-card">
        <span>Trust route</span>
        <strong>Verification status and moderation notes are planned for the next backend phase.</strong>
      </div>
    </div>
  </div>
  <form class="auth-form">
    <p class="eyebrow">Account setup</p>
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
    <p class="form-note">Seller request approval, password hashing, and session handling will be added in the PHP/MySQL phase.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
