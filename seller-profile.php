<?php
require __DIR__ . '/includes/seller_tools.php';

$currentUser = app_require_login();
$sellerUser = seller_build_user_context($currentUser);
$capabilities = seller_capabilities();
$errors = [];
$hasStoreAccess = in_array((string) ($sellerUser['raw_role'] ?? ''), ['seller', 'admin', 'super_admin'], true);
$backPath = $hasStoreAccess ? 'seller-dashboard.php' : app_dashboard_path_for_user($currentUser);
$form = [
    'business_name' => (string) ($sellerUser['business_name'] ?? ''),
    'location' => (string) ($sellerUser['location'] ?? ''),
];

if (app_is_post_request()) {
    $form['business_name'] = trim((string) ($_POST['business_name'] ?? ''));
    $form['location'] = trim((string) ($_POST['location'] ?? ''));
    $intent = (string) ($_POST['intent'] ?? 'save');

    try {
        seller_save_profile($sellerUser, $form, $intent === 'request_verification');
        app_set_flash(
            'success',
            $intent === 'request_verification'
                ? 'Seller verification request submitted successfully.'
                : 'Seller profile updated successfully.'
        );
        app_redirect('seller-profile.php');
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$sellerUser = seller_build_user_context($currentUser);
$hasStoreAccess = in_array((string) ($sellerUser['raw_role'] ?? ''), ['seller', 'admin', 'super_admin'], true);
$pageTitle = $hasStoreAccess ? 'Seller Profile' : 'Become a Seller';
$pageDescription = 'Update your seller profile and submit verification details.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section dashboard">
  <aside class="card sidebar">
    <strong><?php echo $hasStoreAccess ? 'Seller workspace' : 'Seller application'; ?></strong>
    <?php if ($hasStoreAccess): ?>
      <?php foreach (seller_navigation_items() as $item): ?>
        <a class="<?php echo $item['key'] === 'profile' ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['path'])); ?>">
          <?php echo htmlspecialchars($item['label']); ?>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <a href="<?php echo htmlspecialchars(app_url('buyer-dashboard.php')); ?>">Buyer account</a>
      <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
      <a class="is-active" href="<?php echo htmlspecialchars(app_url('seller-profile.php')); ?>">Become a seller</a>
    <?php endif; ?>
  </aside>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow"><?php echo $hasStoreAccess ? 'Seller profile' : 'Seller application'; ?></p>
        <h1><?php echo $hasStoreAccess ? 'Build trust before buyers place orders' : 'Apply to sell on LocalLink'; ?></h1>
      </div>
      <a class="button button-secondary" href="<?php echo htmlspecialchars(app_url($backPath)); ?>"><?php echo $hasStoreAccess ? 'Back to dashboard' : 'Back to account'; ?></a>
    </div>
    <?php if ($errors !== []): ?>
      <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($success = app_pull_flash('success')): ?>
      <div class="notice notice-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error = app_pull_flash('error')): ?>
      <div class="notice notice-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!$capabilities['profile_support']): ?>
      <div class="notice">
        Seller profiles are not available in the current database yet. Import the latest database SQL before using this page.
      </div>
    <?php endif; ?>
    <div class="card split info-card">
      <div class="stack">
        <p class="eyebrow">Seller identity</p>
        <h2><?php echo htmlspecialchars($sellerUser['seller_display_name']); ?></h2>
        <p><?php echo htmlspecialchars((string) $sellerUser['email']); ?></p>
      </div>
      <div class="stack">
        <div class="info-row"><strong>Verification</strong><span><?php echo htmlspecialchars($sellerUser['verification_label']); ?></span></div>
        <div class="info-row"><strong>Location</strong><span><?php echo htmlspecialchars($sellerUser['location'] !== '' ? $sellerUser['location'] : 'Add a trading area'); ?></span></div>
        <div class="info-row"><strong>Joined</strong><span><?php echo htmlspecialchars($sellerUser['joined_on']); ?></span></div>
      </div>
    </div>
    <?php if ($sellerUser['verification_notes'] !== ''): ?>
      <div class="notice">
        Verification feedback: <?php echo htmlspecialchars($sellerUser['verification_notes']); ?>
      </div>
    <?php endif; ?>
    <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('seller-profile.php')); ?>">
      <p class="eyebrow">Profile details</p>
      <div class="field">
        <label for="business-name">Business or display name</label>
        <input id="business-name" name="business_name" type="text" placeholder="Market stall, side hustle, or shop name" value="<?php echo htmlspecialchars($form['business_name']); ?>">
      </div>
      <div class="field">
        <label for="location">Trading location</label>
        <input id="location" name="location" type="text" placeholder="Johannesburg CBD, Soweto, Pretoria East" value="<?php echo htmlspecialchars($form['location']); ?>">
      </div>
      <div class="feature-list">
        <div>Use a seller-facing name buyers will recognize on listings and order updates.</div>
        <div>Add a clear pickup or delivery area so the verification request has useful context.</div>
        <div>Admin approval moves the account into the live seller workspace and keeps the database role in sync.</div>
      </div>
      <div class="form-actions">
        <button class="button" type="submit" name="intent" value="save">Save profile</button>
        <?php if (!in_array((string) $sellerUser['verification_status'], ['pending', 'approved'], true)): ?>
          <button class="button button-secondary" type="submit" name="intent" value="request_verification">Request verification</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
