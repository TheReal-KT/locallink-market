<?php
require __DIR__ . '/includes/app.php';
$currentUser = app_require_buyer();
$orderId = max(0, (int) ($_GET['order_id'] ?? $_POST['order_id'] ?? 0));
$order = $orderId > 0 ? market_get_order_detail($orderId, (int) $currentUser['id'], null) : null;
$errors = [];
$form = [
    'rating' => '5',
    'comment' => '',
];

if ($order === null) {
    $errors[] = 'That order could not be loaded for review.';
}

if (app_is_post_request() && $order !== null) {
    $form['rating'] = trim((string) ($_POST['rating'] ?? '5'));
    $form['comment'] = trim((string) ($_POST['comment'] ?? ''));

    if (!ctype_digit($form['rating']) || (int) $form['rating'] < 1 || (int) $form['rating'] > 5) {
        $errors[] = 'Choose a rating between 1 and 5.';
    }

    if ($form['comment'] === '') {
        $errors[] = 'Add a short review comment.';
    }

    if ($errors === []) {
        try {
            market_create_review_for_order((int) $currentUser['id'], (int) $order['id'], (int) $form['rating'], $form['comment']);
            app_set_flash('success', 'Review submitted successfully.');
            app_redirect('buyer-dashboard.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Review Order';
$pageDescription = 'Leave a review after a completed order.';
require __DIR__ . '/includes/header.php';
?>
<section class="page section split">
  <div class="card stack">
    <p class="eyebrow">Order review</p>
    <h1>Review your purchase</h1>
    <?php if ($order !== null): ?>
      <div class="info-row"><strong>Order</strong><span><?php echo htmlspecialchars((string) ($order['order_number'] ?? '')); ?></span></div>
      <div class="info-row"><strong>Seller</strong><span><?php echo htmlspecialchars((string) ($order['seller_name'] ?? '')); ?></span></div>
      <div class="info-row"><strong>Status</strong><span><?php echo htmlspecialchars((string) ($order['status_label'] ?? '')); ?></span></div>
    <?php endif; ?>
    <p>Reviews are visible to administrators and help build trust on seller listings.</p>
  </div>
  <form class="card form-card" method="post" action="<?php echo htmlspecialchars(app_url('review-order.php')); ?>">
    <input type="hidden" name="order_id" value="<?php echo (int) $orderId; ?>">
    <p class="eyebrow">Review form</p>
    <?php if ($errors !== []): ?>
      <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="field">
      <label for="rating">Rating</label>
      <select id="rating" name="rating">
        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
          <option value="<?php echo $rating; ?>" <?php echo (int) $form['rating'] === $rating ? 'selected' : ''; ?>><?php echo $rating; ?> star<?php echo $rating === 1 ? '' : 's'; ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="field">
      <label for="comment">Comment</label>
      <textarea id="comment" name="comment" rows="6" placeholder="What went well with the order?"><?php echo htmlspecialchars($form['comment']); ?></textarea>
    </div>
    <button class="button" type="submit" <?php echo $order === null ? 'disabled' : ''; ?>>Submit review</button>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
