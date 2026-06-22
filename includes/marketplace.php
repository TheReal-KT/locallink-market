<?php

function market_require_database(): PDO
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    return $pdo;
}

function market_admin_log(int $adminId, string $action, string $entityType = '', int $entityId = 0, string $details = ''): void
{
    if (!market_table_exists('admin_logs')) {
        return;
    }

    try {
        $pdo = market_require_database();
        $statement = $pdo->prepare(
            'INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, details)
             VALUES (:admin_id, :action, :entity_type, :entity_id, :details)'
        );
        $statement->execute([
            'admin_id' => $adminId,
            'action' => $action,
            'entity_type' => $entityType !== '' ? $entityType : null,
            'entity_id' => $entityId > 0 ? $entityId : null,
            'details' => $details !== '' ? $details : null,
        ]);
    } catch (Throwable $exception) {
        return;
    }
}

function market_get_seller_profile(int $userId): array
{
    $default = [
        'id' => 0,
        'user_id' => $userId,
        'business_name' => '',
        'location' => '',
        'phone_number' => '',
        'bio' => '',
        'verification_status' => 'not_requested',
        'verification_status_label' => 'Not requested',
        'verification_notes' => '',
        'requested_at' => '',
        'reviewed_at' => '',
    ];

    if ($userId === 3 && !db_is_available()) {
        $default['id'] = 1;
        $default['business_name'] = 'Anele Finds';
        $default['location'] = 'Soweto, Johannesburg';
        $default['phone_number'] = '0825550138';
        $default['bio'] = 'Affordable study, fashion, and tech items sourced from local sellers.';
        $default['verification_status'] = 'approved';
        $default['verification_status_label'] = 'Approved';
        $default['verification_notes'] = 'Approved for demo seller access.';
        $default['requested_at'] = '2026-06-10 09:00:00';
        $default['reviewed_at'] = '2026-06-10 10:00:00';
        return $default;
    }

    if (!db_is_available() || !market_table_exists('seller_profiles')) {
        return $default;
    }

    $pdo = market_require_database();
    $statement = $pdo->prepare(
        'SELECT *
         FROM seller_profiles
         WHERE user_id = :user_id
         LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $row = $statement->fetch();

    if (!$row) {
        return $default;
    }

    $status = strtolower((string) ($row['verification_status'] ?? 'not_requested'));
    $row['verification_status'] = $status;
    $row['verification_status_label'] = ucfirst(str_replace('_', ' ', $status));

    return array_merge($default, $row);
}

function market_upsert_seller_profile(int $userId, array $input, bool $submitForReview = false): void
{
    $pdo = market_require_database();
    $current = market_get_seller_profile($userId);

    $payload = [
        'business_name' => trim((string) ($input['business_name'] ?? '')),
        'location' => trim((string) ($input['location'] ?? '')),
        'phone_number' => trim((string) ($input['phone_number'] ?? '')),
        'bio' => trim((string) ($input['bio'] ?? '')),
        'verification_status' => $submitForReview ? 'pending' : (string) ($current['verification_status'] ?? 'not_requested'),
        'verification_notes' => $submitForReview ? '' : (string) ($current['verification_notes'] ?? ''),
    ];

    if ((int) ($current['id'] ?? 0) > 0) {
        $statement = $pdo->prepare(
            'UPDATE seller_profiles
             SET business_name = :business_name,
                 location = :location,
                 phone_number = :phone_number,
                 bio = :bio,
                 verification_status = :verification_status,
                 verification_notes = :verification_notes,
                 requested_at = CASE WHEN :submit_for_review = 1 THEN CURRENT_TIMESTAMP ELSE requested_at END
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId,
            'business_name' => $payload['business_name'],
            'location' => $payload['location'],
            'phone_number' => $payload['phone_number'],
            'bio' => $payload['bio'],
            'verification_status' => $payload['verification_status'],
            'verification_notes' => $payload['verification_notes'],
            'submit_for_review' => $submitForReview ? 1 : 0,
        ]);
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO seller_profiles (user_id, business_name, location, phone_number, bio, verification_status, verification_notes, requested_at)
         VALUES (:user_id, :business_name, :location, :phone_number, :bio, :verification_status, :verification_notes, :requested_at)'
    );
    $statement->execute([
        'user_id' => $userId,
        'business_name' => $payload['business_name'],
        'location' => $payload['location'],
        'phone_number' => $payload['phone_number'],
        'bio' => $payload['bio'],
        'verification_status' => $payload['verification_status'],
        'verification_notes' => $payload['verification_notes'],
        'requested_at' => $submitForReview ? date('Y-m-d H:i:s') : null,
    ]);
}

function market_get_seller_requests(int $limit = 0): array
{
    if (!db_is_available() || !market_table_exists('seller_profiles')) {
        return [];
    }

    $pdo = market_require_database();
    $limitSql = $limit > 0 ? ' LIMIT ' . max(1, $limit) : '';
    $statement = $pdo->query(
        'SELECT sp.id,
                sp.user_id,
                u.full_name,
                u.email,
                sp.business_name,
                sp.location,
                sp.phone_number,
                sp.bio,
                sp.verification_status,
                sp.verification_notes,
                sp.requested_at,
                sp.reviewed_at
         FROM seller_profiles sp
         INNER JOIN users u ON u.id = sp.user_id
         WHERE sp.verification_status IN ("pending", "rejected", "approved")
         ORDER BY CASE sp.verification_status WHEN "pending" THEN 0 ELSE 1 END, sp.requested_at DESC, sp.created_at DESC' . $limitSql
    );

    return array_map(static function (array $row): array {
        $status = strtolower((string) ($row['verification_status'] ?? 'pending'));
        $row['verification_status'] = $status;
        $row['verification_status_label'] = ucfirst(str_replace('_', ' ', $status));
        $row['requested_on'] = market_format_date((string) ($row['requested_at'] ?? ''));
        return $row;
    }, $statement->fetchAll());
}

function market_review_seller_request(int $profileId, string $decision, string $notes, int $adminId): void
{
    $pdo = market_require_database();
    $decision = strtolower($decision);

    if (!in_array($decision, ['approved', 'rejected'], true)) {
        throw new RuntimeException('Choose a valid seller verification decision.');
    }

    $statement = $pdo->prepare(
        'SELECT user_id
         FROM seller_profiles
         WHERE id = :profile_id
         LIMIT 1'
    );
    $statement->execute(['profile_id' => $profileId]);
    $userId = $statement->fetchColumn();

    if ($userId === false) {
        throw new RuntimeException('That seller request could not be found.');
    }

    $pdo->beginTransaction();

    try {
        $updateProfile = $pdo->prepare(
            'UPDATE seller_profiles
             SET verification_status = :verification_status,
                 verification_notes = :verification_notes,
                 reviewed_at = CURRENT_TIMESTAMP
             WHERE id = :profile_id'
        );
        $updateProfile->execute([
            'verification_status' => $decision,
            'verification_notes' => trim($notes),
            'profile_id' => $profileId,
        ]);

        $updateUser = $pdo->prepare(
            'UPDATE users
             SET role = :role,
                 is_admin = :is_admin
             WHERE id = :user_id'
        );
        $updateUser->execute([
            'role' => $decision === 'approved' ? 'seller' : 'buyer',
            'is_admin' => 0,
            'user_id' => (int) $userId,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    market_admin_log($adminId, $decision === 'approved' ? 'approved_seller' : 'rejected_seller', 'seller_profiles', $profileId, trim($notes));
}

function market_get_manageable_users(): array
{
    if (!db_is_available()) {
        return array_map(static function (array $user): array {
            $user = market_normalize_user($user);
            return [
                'id' => (int) $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'role_label' => market_role_label($user['role']),
                'status' => (string) $user['status'],
                'status_label' => ucfirst((string) $user['status']),
                'joined' => market_format_date((string) ($user['created_at'] ?? '')),
                'verification_status' => $user['role'] === 'seller' ? 'approved' : 'not_requested',
                'verification_status_label' => $user['role'] === 'seller' ? 'Approved' : 'Not requested',
            ];
        }, market_sample_users());
    }

    $pdo = market_require_database();
    $statement = $pdo->query(
        'SELECT ' . market_user_select_columns() . ',
                COALESCE(sp.verification_status, "not_requested") AS verification_status
         FROM users u
         LEFT JOIN seller_profiles sp ON sp.user_id = u.id
         ORDER BY u.created_at DESC'
    );

    return array_map(static function (array $row): array {
        $row = market_normalize_user($row);
        $verificationStatus = strtolower((string) ($row['verification_status'] ?? 'not_requested'));
        return [
            'id' => (int) $row['id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'role_label' => market_role_label($row['role']),
            'status' => (string) $row['status'],
            'status_label' => ucfirst((string) $row['status']),
            'joined' => market_format_date((string) ($row['created_at'] ?? '')),
            'verification_status' => $verificationStatus,
            'verification_status_label' => ucfirst(str_replace('_', ' ', $verificationStatus)),
        ];
    }, $statement->fetchAll());
}

function market_update_user_admin(int $userId, array $input, int $adminId): void
{
    $pdo = market_require_database();
    $role = strtolower((string) ($input['role'] ?? 'buyer'));
    $status = strtolower((string) ($input['status'] ?? 'active'));

    if (!in_array($role, ['buyer', 'seller', 'admin'], true)) {
        throw new RuntimeException('Choose a valid user role.');
    }

    if (!in_array($status, ['active', 'disabled'], true)) {
        throw new RuntimeException('Choose a valid user status.');
    }

    $statement = $pdo->prepare(
        'UPDATE users
         SET role = :role,
             status = :status,
             is_admin = :is_admin
         WHERE id = :user_id'
    );
    $statement->execute([
        'role' => $role,
        'status' => $status,
        'is_admin' => $role === 'admin' ? 1 : 0,
        'user_id' => $userId,
    ]);

    if ($role === 'seller' && market_table_exists('seller_profiles')) {
        market_upsert_seller_profile($userId, [], false);
    }

    market_admin_log($adminId, 'updated_user', 'users', $userId, 'Role: ' . $role . ', status: ' . $status);
}

function market_get_manageable_categories(): array
{
    $categories = market_get_categories();
    usort($categories, static function (array $left, array $right): int {
        return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
    });

    return $categories;
}

function market_create_category_record(string $name, int $adminId): int
{
    $pdo = market_require_database();
    $statement = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
    $statement->execute(['name' => trim($name)]);
    $categoryId = (int) $pdo->lastInsertId();
    market_admin_log($adminId, 'created_category', 'categories', $categoryId, trim($name));
    return $categoryId;
}

function market_update_category_record(int $categoryId, string $name, int $adminId): void
{
    $pdo = market_require_database();
    $statement = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :category_id');
    $statement->execute([
        'name' => trim($name),
        'category_id' => $categoryId,
    ]);
    market_admin_log($adminId, 'updated_category', 'categories', $categoryId, trim($name));
}

function market_delete_category_record(int $categoryId, int $adminId): void
{
    $pdo = market_require_database();
    $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :category_id');
    $check->execute(['category_id' => $categoryId]);
    if ((int) $check->fetchColumn() > 0) {
        throw new RuntimeException('Move or archive products before deleting that category.');
    }

    $statement = $pdo->prepare('DELETE FROM categories WHERE id = :category_id');
    $statement->execute(['category_id' => $categoryId]);
    market_admin_log($adminId, 'deleted_category', 'categories', $categoryId);
}

function market_get_manageable_products(array $filters = []): array
{
    $filters['include_non_public'] = true;
    if (!isset($filters['status'])) {
        $filters['status'] = ['active', 'inactive', 'pending_review', 'archived'];
    }
    return market_get_products($filters);
}

function market_get_manageable_product_by_id(int $productId): ?array
{
    return market_get_product_by_id($productId, true);
}

function market_get_seller_list(): array
{
    $users = market_get_manageable_users();
    return array_values(array_filter($users, static function (array $user): bool {
        return in_array((string) ($user['role'] ?? ''), ['seller', 'admin'], true) || (string) ($user['verification_status'] ?? '') === 'approved';
    }));
}

function market_create_seller_product(int $sellerId, array $input): int
{
    return market_create_product(array_merge($input, [
        'seller_id' => $sellerId,
        'status' => $input['status'] ?? 'pending_review',
    ]));
}

function market_update_seller_product(int $sellerId, int $productId, array $input): void
{
    $product = market_get_manageable_product_by_id($productId);

    if ($product === null || (int) ($product['seller_id'] ?? 0) !== $sellerId) {
        throw new RuntimeException('That listing does not belong to this seller.');
    }

    if (!isset($input['status'])) {
        $input['status'] = 'pending_review';
    }

    market_update_product($productId, $input);
}

function market_get_orders_admin(array $filters = [], int $limit = 0): array
{
    return market_fetch_order_summaries($filters, $limit);
}

function market_get_order_detail(int $orderId, ?int $buyerId = null, ?int $sellerId = null): ?array
{
    if (!db_is_available()) {
        return null;
    }

    $pdo = market_require_database();
    $params = ['order_id' => $orderId];
    $conditions = ['o.id = :order_id'];

    if ($buyerId !== null) {
        $conditions[] = 'o.user_id = :buyer_id';
        $params['buyer_id'] = $buyerId;
    }

    if ($sellerId !== null) {
        $conditions[] = 'o.seller_id = :seller_id';
        $params['seller_id'] = $sellerId;
    }

    $statement = $pdo->prepare(
        'SELECT o.id,
                o.order_number,
                o.user_id,
                o.seller_id,
                o.status,
                o.payment_status,
                o.delivery_method,
                o.subtotal_amount,
                o.delivery_fee,
                o.total_amount,
                o.buyer_note,
                o.created_at,
                buyer.full_name AS buyer_name,
                buyer.email AS buyer_email,
                COALESCE(seller.full_name, "Unassigned seller") AS seller_name,
                oa.contact_name,
                oa.phone_number,
                oa.address_line_1,
                oa.address_line_2,
                oa.city,
                oa.postal_code,
                oa.collection_note,
                op.payment_method,
                op.provider_reference,
                op.paid_at,
                r.id AS review_id,
                r.rating,
                r.comment,
                r.status AS review_status
         FROM orders o
         INNER JOIN users buyer ON buyer.id = o.user_id
         LEFT JOIN users seller ON seller.id = o.seller_id
         LEFT JOIN order_addresses oa ON oa.order_id = o.id
         LEFT JOIN order_payments op ON op.order_id = o.id
         LEFT JOIN reviews r ON r.order_id = o.id
         WHERE ' . implode(' AND ', $conditions) . '
         LIMIT 1'
    );
    $statement->execute($params);
    $row = $statement->fetch();

    if (!$row) {
        return null;
    }

    $itemsStatement = $pdo->prepare(
        'SELECT p.title,
                oi.quantity,
                oi.unit_price,
                oi.line_total
         FROM order_items oi
         INNER JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = :order_id
         ORDER BY oi.id ASC'
    );
    $itemsStatement->execute(['order_id' => $orderId]);

    $row['items'] = array_map(static function (array $item): array {
        return [
            'title' => (string) ($item['title'] ?? ''),
            'quantity' => (int) ($item['quantity'] ?? 0),
            'unit_price' => market_format_money((float) ($item['unit_price'] ?? 0)),
            'line_total' => market_format_money((float) ($item['line_total'] ?? 0)),
        ];
    }, $itemsStatement->fetchAll());
    $row['status_label'] = market_humanize_order_status((string) ($row['status'] ?? 'pending'));
    $row['payment_status_label'] = market_humanize_payment_status((string) ($row['payment_status'] ?? 'pending'));
    $row['payment_method_label'] = market_humanize_payment_method((string) ($row['payment_method'] ?? ''));
    $row['delivery_method_label'] = market_humanize_delivery_method((string) ($row['delivery_method'] ?? 'collection'));
    $row['subtotal'] = market_format_money((float) ($row['subtotal_amount'] ?? 0));
    $row['delivery_fee_formatted'] = market_format_money((float) ($row['delivery_fee'] ?? 0));
    $row['total'] = market_format_money((float) ($row['total_amount'] ?? 0));

    return $row;
}

function market_assert_valid_order_status(string $status): string
{
    $status = strtolower($status);
    if (!in_array($status, ['pending', 'paid', 'processing', 'ready', 'completed', 'cancelled'], true)) {
        throw new RuntimeException('Choose a valid order status.');
    }

    return $status;
}

function market_update_order_status_admin(int $orderId, string $status, int $adminId): void
{
    $pdo = market_require_database();
    $status = market_assert_valid_order_status($status);
    $statement = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :order_id');
    $statement->execute([
        'status' => $status,
        'order_id' => $orderId,
    ]);
    market_admin_log($adminId, 'updated_order_status', 'orders', $orderId, $status);
}

function market_update_order_status_seller(int $orderId, int $sellerId, string $status): void
{
    $pdo = market_require_database();
    $status = market_assert_valid_order_status($status);
    $statement = $pdo->prepare(
        'UPDATE orders
         SET status = :status
         WHERE id = :order_id AND seller_id = :seller_id'
    );
    $statement->execute([
        'status' => $status,
        'order_id' => $orderId,
        'seller_id' => $sellerId,
    ]);

    if ($statement->rowCount() < 1) {
        throw new RuntimeException('That order could not be updated for this seller.');
    }
}

function market_get_manageable_reviews(): array
{
    if (!db_is_available() || !market_table_exists('reviews')) {
        return [];
    }

    $pdo = market_require_database();
    $statement = $pdo->query(
        'SELECT r.id,
                r.order_id,
                r.rating,
                r.comment,
                r.status,
                r.created_at,
                buyer.full_name AS reviewer_name,
                seller.full_name AS seller_name,
                o.order_number
         FROM reviews r
         INNER JOIN users buyer ON buyer.id = r.reviewer_id
         INNER JOIN users seller ON seller.id = r.seller_id
         INNER JOIN orders o ON o.id = r.order_id
         ORDER BY r.created_at DESC'
    );

    return array_map(static function (array $row): array {
        $status = strtolower((string) ($row['status'] ?? 'visible'));
        $row['status'] = $status;
        $row['status_label'] = market_humanize_review_status($status);
        $row['created_on'] = market_format_date((string) ($row['created_at'] ?? ''));
        return $row;
    }, $statement->fetchAll());
}

function market_get_seller_reviews(int $sellerId, int $limit = 10): array
{
    if (!db_is_available() || !market_table_exists('reviews')) {
        return [];
    }

    $pdo = market_require_database();
    $statement = $pdo->prepare(
        'SELECT r.id,
                r.order_id,
                r.rating,
                r.comment,
                r.status,
                r.created_at,
                buyer.full_name AS reviewer_name,
                o.order_number
         FROM reviews r
         INNER JOIN users buyer ON buyer.id = r.reviewer_id
         INNER JOIN orders o ON o.id = r.order_id
         WHERE r.seller_id = :seller_id AND r.status = "visible"
         ORDER BY r.created_at DESC
         LIMIT ' . max(1, $limit)
    );
    $statement->execute(['seller_id' => $sellerId]);

    return $statement->fetchAll();
}

function market_get_seller_rating_summary(int $sellerId): array
{
    if (!db_is_available() || !market_table_exists('reviews')) {
        return ['average' => 0.0, 'count' => 0, 'label' => 'No reviews yet'];
    }

    $pdo = market_require_database();
    $statement = $pdo->prepare(
        'SELECT ROUND(AVG(rating), 1) AS average_rating,
                COUNT(*) AS review_count
         FROM reviews
         WHERE seller_id = :seller_id AND status = "visible"'
    );
    $statement->execute(['seller_id' => $sellerId]);
    $row = $statement->fetch() ?: [];
    $average = round((float) ($row['average_rating'] ?? 0), 1);
    $count = (int) ($row['review_count'] ?? 0);

    return [
        'average' => $average,
        'count' => $count,
        'label' => $count > 0 ? $average . ' / 5 from ' . $count . ' review' . ($count === 1 ? '' : 's') : 'No reviews yet',
    ];
}

function market_update_review_visibility(int $reviewId, string $status, int $adminId): void
{
    $status = strtolower($status);
    if (!in_array($status, ['visible', 'hidden'], true)) {
        throw new RuntimeException('Choose a valid review status.');
    }

    $pdo = market_require_database();
    $statement = $pdo->prepare('UPDATE reviews SET status = :status WHERE id = :review_id');
    $statement->execute([
        'status' => $status,
        'review_id' => $reviewId,
    ]);
    market_admin_log($adminId, 'updated_review_status', 'reviews', $reviewId, $status);
}

function market_get_reviewable_orders(int $buyerId): array
{
    return array_values(array_filter(market_get_buyer_orders($buyerId), static function (array $order): bool {
        return !empty($order['can_review']);
    }));
}

function market_create_review_for_order(int $buyerId, int $orderId, int $rating, string $comment): void
{
    $pdo = market_require_database();

    if ($rating < 1 || $rating > 5) {
        throw new RuntimeException('Choose a rating between 1 and 5.');
    }

    $detail = market_get_order_detail($orderId, $buyerId, null);
    if ($detail === null) {
        throw new RuntimeException('That order could not be found.');
    }

    if ((string) ($detail['status'] ?? '') !== 'completed') {
        throw new RuntimeException('Only completed orders can be reviewed.');
    }

    if (!empty($detail['review_id'])) {
        throw new RuntimeException('A review already exists for that order.');
    }

    $sellerId = (int) ($detail['seller_id'] ?? 0);
    if ($sellerId < 1) {
        throw new RuntimeException('That order is not linked to a seller yet.');
    }

    $statement = $pdo->prepare(
        'INSERT INTO reviews (order_id, reviewer_id, seller_id, rating, comment, status)
         VALUES (:order_id, :reviewer_id, :seller_id, :rating, :comment, "visible")'
    );
    $statement->execute([
        'order_id' => $orderId,
        'reviewer_id' => $buyerId,
        'seller_id' => $sellerId,
        'rating' => $rating,
        'comment' => trim($comment),
    ]);
}

