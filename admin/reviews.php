<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_review_source(): array
{
    $table = admin_table_from_candidates(['reviews', 'product_reviews']);
    return ['mode' => $table === null ? 'demo' : 'database', 'table' => $table];
}

function admin_review_rows(string $statusFilter): array
{
    $source = admin_review_source();

    if ($source['table'] === null || !db_is_available()) {
        $rows = admin_demo_rows('reviews', [
            ['id' => 1, 'product_title' => 'Canvas street backpack', 'reviewer_name' => 'Nandi P.', 'rating' => 5, 'body' => 'Great everyday bag and it still looks new after two weeks.', 'status' => 'pending', 'created_at' => '2026-06-18 08:30:00'],
            ['id' => 2, 'product_title' => 'Minimal desk lamp', 'reviewer_name' => 'Thato M.', 'rating' => 3, 'body' => 'The light is good but the cable is shorter than expected.', 'status' => 'approved', 'created_at' => '2026-06-17 17:10:00'],
        ]);
    } else {
        $pdo = db_try_get_connection();
        $table = $source['table'];
        $bodySelect = market_table_has_column($table, 'comment') ? 'r.comment' : (market_table_has_column($table, 'body') ? 'r.body' : (market_table_has_column($table, 'message') ? 'r.message' : "''"));
        $createdAtSelect = market_table_has_column($table, 'created_at') ? 'r.created_at' : (market_table_has_column($table, 'submitted_at') ? 'r.submitted_at' : 'NULL');
        $statusSelect = market_table_has_column($table, 'status') ? 'r.status' : "'pending'";
        $ratingSelect = market_table_has_column($table, 'rating') ? 'r.rating' : '0';
        $userJoin = market_table_has_column($table, 'user_id') ? 'LEFT JOIN users u ON u.id = r.user_id' : '';
        $productJoin = market_table_has_column($table, 'product_id') ? 'LEFT JOIN products p ON p.id = r.product_id' : '';
        $reviewerSelect = market_table_has_column($table, 'user_id') ? 'COALESCE(u.full_name, "Unknown reviewer")' : "'Unknown reviewer'";
        $productSelect = market_table_has_column($table, 'product_id') ? 'COALESCE(p.title, "Unlinked product")' : "'Unlinked product'";
        $statement = $pdo->query('SELECT r.id, ' . $productSelect . ' AS product_title, ' . $reviewerSelect . ' AS reviewer_name, ' . $ratingSelect . ' AS rating, ' . $bodySelect . ' AS body, ' . $statusSelect . ' AS status, ' . $createdAtSelect . ' AS created_at FROM ' . $table . ' r ' . $userJoin . ' ' . $productJoin . ' ORDER BY created_at DESC, r.id DESC');
        $rows = array_map(static function (array $row): array {
            return ['id' => (int) $row['id'], 'product_title' => (string) $row['product_title'], 'reviewer_name' => (string) $row['reviewer_name'], 'rating' => (int) $row['rating'], 'body' => (string) $row['body'], 'status' => strtolower((string) $row['status']), 'created_at' => $row['created_at'] !== null ? (string) $row['created_at'] : null];
        }, $statement->fetchAll());
    }

    return array_values(array_filter($rows, static function (array $row) use ($statusFilter): bool {
        return $statusFilter === 'all' || ($row['status'] ?? '') === $statusFilter;
    }));
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    try {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'pending');
        if (!in_array($status, ['pending', 'approved', 'hidden'], true)) {
            throw new RuntimeException('Choose a valid review status.');
        }
        $source = admin_review_source();
        if ($source['table'] === null || !db_is_available()) {
            admin_set_demo_row_override('reviews', $reviewId, ['status' => $status]);
        } else {
            if (!market_table_has_column($source['table'], 'status')) {
                throw new RuntimeException('The shared review table does not expose a status column yet.');
            }
            $pdo = db_try_get_connection();
            $statement = $pdo->prepare('UPDATE ' . $source['table'] . ' SET status = :status WHERE id = :review_id');
            $statement->execute(['review_id' => $reviewId, 'status' => $status]);
        }
        app_set_flash('success', 'Review moderation updated.');
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
    }

    admin_redirect('reviews.php');
}

$statusFilter = trim((string) ($_GET['status'] ?? 'all'));
$source = admin_review_source();
$reviews = admin_review_rows($statusFilter);
$notices = admin_collect_notices();
$pageTitle = 'Admin Reviews';
$pageDescription = 'Moderate product reviews and visible feedback.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('reviews'); ?>
  <div class="stack">
    <div class="section-head"><div><p class="eyebrow">Review moderation</p><h1>Feedback queue</h1></div><p>This page switches to a live review table when one exists; otherwise it stays usable with a demo queue.</p></div>
    <?php admin_render_notices($notices); ?>
    <div class="card info-card"><div class="info-row"><strong>Data source</strong><span><?php echo $source['mode'] === 'database' ? 'Live review table detected' : 'Demo queue active'; ?></span></div></div>
    <form class="card" method="get" action="<?php echo htmlspecialchars(app_url('admin/reviews.php')); ?>">
      <div class="field-row">
        <div class="field"><label for="review-status">Status</label><select id="review-status" name="status"><option value="all">All review states</option><option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option><option value="hidden" <?php echo $statusFilter === 'hidden' ? 'selected' : ''; ?>>Hidden</option></select></div>
        <div class="form-actions"><button class="button" type="submit">Apply filter</button><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/reviews.php')); ?>">Clear</a></div>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">Review list</p><h2><?php echo count($reviews); ?> review<?php echo count($reviews) === 1 ? '' : 's'; ?></h2></div></div>
      <table>
        <thead><tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Submitted</th><th>Moderation</th></tr></thead>
        <tbody>
          <?php if ($reviews === []): ?>
            <tr><td colspan="6">No reviews matched the current filter.</td></tr>
          <?php else: ?>
            <?php foreach ($reviews as $review): ?>
              <tr>
                <td><?php echo htmlspecialchars($review['product_title']); ?></td>
                <td><?php echo htmlspecialchars($review['reviewer_name']); ?></td>
                <td><?php echo (int) $review['rating']; ?>/5</td>
                <td><?php echo htmlspecialchars($review['body']); ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($review['created_at'])); ?></td>
                <td>
                  <form class="stack" method="post" action="<?php echo htmlspecialchars(app_url('admin/reviews.php')); ?>">
                    <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                    <div class="field"><label>Status</label><select name="status"><option value="pending" <?php echo $review['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="approved" <?php echo $review['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option><option value="hidden" <?php echo $review['status'] === 'hidden' ? 'selected' : ''; ?>>Hidden</option></select></div>
                    <button class="button" type="submit">Save</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
