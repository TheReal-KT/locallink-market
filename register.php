<?php
require __DIR__ . '/includes/app.php';

$currentUser = app_current_user();
if ($currentUser !== null) {
    app_redirect(app_dashboard_path_for_user($currentUser));
}

$errors = [];
$form = [
    'name' => '',
    'email' => '',
];

if (app_is_post_request()) {
    $form['name'] = trim((string) ($_POST['name'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($form['name'] === '') {
        $errors[] = 'Enter your full name.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Use a password with at least 8 characters.';
    }

    if ($errors === []) {
        try {
            market_create_user($form['name'], $form['email'], $password, 'buyer');
            app_set_flash('success', 'Account created successfully. Sign in to continue.');
            app_redirect('login.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Register';
$pageDescription = 'Create a buyer account and apply for seller access later.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card stack">
    <p class="eyebrow">Create account</p>
    <h1>Register for LocalLink</h1>
    <p>Public registration creates a buyer account first. After you sign in, you can apply for seller access from your account area.</p>
    <div class="feature-list">
      <div>Browse and order products as a buyer</div>
      <div>View your order history and leave reviews</div>
      <div>Apply for seller verification after signup</div>
    </div>
  </div>
  <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('register.php')); ?>">
    <p class="eyebrow">Registration form</p>
    <?php if ($errors !== []): ?>
      <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="field">
      <label for="name">Full name</label>
      <input id="name" name="name" type="text" placeholder="Full name" value="<?php echo htmlspecialchars($form['name']); ?>">
    </div>
    <div class="field">
      <label for="register-email">Email address</label>
      <input id="register-email" name="email" type="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($form['email']); ?>">
    </div>
    <div class="field">
      <label for="register-password">Password</label>
      <input id="register-password" name="password" type="password" placeholder="Create password">
    </div>
    <button class="button" type="submit">Create account</button>
    <p class="hint">Use at least 8 characters. Seller and admin access are granted after registration, not through the public form.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
