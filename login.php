<?php
require __DIR__ . '/includes/app.php';

$currentUser = app_current_user();
if ($currentUser !== null) {
    app_redirect(app_dashboard_path_for_user($currentUser));
}

$flashSuccess = app_pull_flash('success');
$flashError = app_pull_flash('error');
$errorMessage = null;
$email = '';

if (app_is_post_request()) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errorMessage = 'Enter a valid email address and password.';
    } else {
        try {
            $user = market_authenticate_user($email, $password);

            if ($user === null) {
                $errorMessage = 'Incorrect email address or password.';
            } else {
                app_login_user($user);
                app_set_flash('success', 'Welcome back, ' . $user['full_name'] . '.');
                app_redirect(app_dashboard_path_for_user($user));
            }
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }
    }
}

$pageTitle = 'Login';
$pageDescription = 'Sign in with email and password.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card stack">
    <p class="eyebrow">Account login</p>
    <h1>Sign in</h1>
    <p>This project now uses only standard email and password login. Social login and role-heavy auth were removed.</p>
    <div class="feature-list">
      <div>Customer dashboard for order history</div>
      <div>Admin dashboard for product and store oversight</div>
      <div>Simple seeded demo accounts after importing the SQL files</div>
    </div>
    <div class="card subtle-card">
      <strong>Demo accounts</strong>
      <p><strong>Customer:</strong> buyer@locallink.market / Buyer123!</p>
      <p><strong>Admin:</strong> admin@locallink.market / Admin123!</p>
    </div>
  </div>
  <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('login.php')); ?>">
    <p class="eyebrow">Login form</p>
    <?php if ($flashSuccess !== null): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== null): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    <div class="field">
      <label for="email">Email address</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="Enter password">
    </div>
    <button class="button" type="submit">Sign in</button>
    <a class="text-link" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create a new account</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
