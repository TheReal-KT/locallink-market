<?php
require dirname(__DIR__) . '/includes/admin_tools.php';

function admin_verification_rows(string $statusFilter): array
{
    if (!db_is_available() || !market_table_exists('seller_profiles')) {
        $rows = admin_demo_rows('verification', [
            ['id' => 1, 'seller_name' => 'Naledi Crafts', 'owner_name' => 'Naledi T.', 'email' => 'naledi@locallink.market', 'location' => 'Soweto, Johannesburg', 'status' => 'pending', 'note' => 'Awaiting admin review.', 'submitted_at' => '2026-06-19 11:20:00'],
            ['id' => 2, 'seller_name' => 'Campus Tech Fix', 'owner_name' => 'Mpho K.', 'email' => 'mpho@locallink.market', 'location' => 'Pretoria Central', 'status' => 'approved', 'note' => 'Approved in the demo queue.', 'submitted_at' => '2026-06-16 09:05:00'],
        ]);
    } else {
        $rows = array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'seller_name' => trim((string) ($row['business_name'] ?? '')) !== '' ? (string) $row['business_name'] : (string) ($row['full_name'] ?? 'Seller account'),
                'owner_name' => (string) ($row['full_name'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'location' => (string) ($row['location'] ?? ''),
                'status' => strtolower((string) ($row['verification_status'] ?? 'pending')),
                'status_label' => (string) ($row['verification_status_label'] ?? admin_humanize((string) ($row['verification_status'] ?? 'pending'))),
                'note' => (string) ($row['verification_notes'] ?? ''),
                'submitted_at' => (string) ($row['requested_at'] ?? ''),
            ];
        }, market_get_seller_requests());
    }

    return array_values(array_filter($rows, static function (array $row) use ($statusFilter): bool {
        return $statusFilter === 'all' || ($row['status'] ?? '') === $statusFilter;
    }));
}

$currentUser = app_require_admin();

if (app_is_post_request()) {
    try {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $status = strtolower(trim((string) ($_POST['status'] ?? 'pending')));
        $note = trim((string) ($_POST['note'] ?? ''));

        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw new RuntimeException('Choose a valid verification status.');
        }

        if (!db_is_available() || !market_table_exists('seller_profiles')) {
            admin_set_demo_row_override('verification', $requestId, ['status' => $status, 'status_label' => admin_humanize($status), 'note' => $note === '' ? 'Updated in the demo queue.' : $note]);
        } else {
            if ($status === 'pending') {
                throw new RuntimeException('Use approved or rejected when reviewing a live seller request.');
            }

            market_review_seller_request($requestId, $status, $note, (int) $currentUser['id']);
        }

        app_set_flash('success', 'Verification request updated.');
    } catch (Throwable $exception) {
        app_set_flash('error', $exception->getMessage());
    }

    admin_redirect('verification.php');
}

$statusFilter = trim((string) ($_GET['status'] ?? 'all'));
$requests = admin_verification_rows($statusFilter);
$notices = admin_collect_notices();
$liveMode = db_is_available() && market_table_exists('seller_profiles');
$pageTitle = 'Admin Verification';
$pageDescription = 'Review seller verification requests and capture approval notes.';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page section dashboard">
  <?php admin_render_sidebar('verification'); ?>
  <div class="stack">
    <div class="section-head">
      <div>
        <p class="eyebrow">Seller verification</p>
        <h1>Approval queue</h1>
      </div>
      <p><?php echo $liveMode ? 'Live seller applications from seller_profiles.' : 'Demo verification queue active because the seller_profiles table is not available.'; ?></p>
    </div>
    <?php admin_render_notices($notices); ?>
    <div class="card info-card"><div class="info-row"><strong>Data source</strong><span><?php echo $liveMode ? 'seller_profiles table' : 'Demo queue'; ?></span></div></div>
    <form class="card" method="get" action="<?php echo htmlspecialchars(app_url('admin/verification.php')); ?>">
      <div class="field-row">
        <div class="field"><label for="verification-status">Status</label><select id="verification-status" name="status"><option value="all">All request states</option><option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option><option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option></select></div>
        <div class="form-actions"><button class="button" type="submit">Apply filter</button><a class="text-link" href="<?php echo htmlspecialchars(app_url('admin/verification.php')); ?>">Clear</a></div>
      </div>
    </form>
    <section class="card table-card">
      <div class="section-head"><div><p class="eyebrow">Verification requests</p><h2><?php echo count($requests); ?> request<?php echo count($requests) === 1 ? '' : 's'; ?></h2></div></div>
      <table>
        <thead><tr><th>Seller</th><th>Owner</th><th>Location</th><th>Submitted</th><th>Review</th></tr></thead>
        <tbody>
          <?php if ($requests === []): ?>
            <tr><td colspan="5">No verification requests matched the current filter.</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $request): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($request['seller_name']); ?></strong><br><span class="hint"><?php echo htmlspecialchars($request['email']); ?></span></td>
                <td><?php echo htmlspecialchars($request['owner_name']); ?></td>
                <td><?php echo htmlspecialchars($request['location'] !== '' ? $request['location'] : 'Not supplied'); ?></td>
                <td><?php echo htmlspecialchars(admin_format_datetime($request['submitted_at'])); ?></td>
                <td>
                  <form class="stack" method="post" action="<?php echo htmlspecialchars(app_url('admin/verification.php')); ?>">
                    <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                    <div class="field"><label>Status</label><select name="status"><option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="approved" <?php echo $request['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option><option value="rejected" <?php echo $request['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option></select></div>
                    <div class="field"><label>Admin note</label><textarea name="note" rows="3"><?php echo htmlspecialchars($request['note']); ?></textarea></div>
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
