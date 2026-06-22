<?php

function market_database_unavailable_message(): string
{
    $config = db_config();
    $message = sprintf(
        'Database is not connected yet. Expected MySQL database "%s" on %s:%d for user "%s". Import database/schema.sql and database/seed.sql, then update the LocalLink database settings.',
        $config['name'],
        $config['host'],
        $config['port'],
        $config['user']
    );
    $lastError = db_last_error();

    if ($lastError !== null) {
        $message .= ' Last error: ' . $lastError;
    }

    return $message;
}

function market_format_money(float $amount): string
{
    return 'R ' . number_format($amount, 2, '.', ' ');
}

function market_format_date(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp ? date('j M Y', $timestamp) : '-';
}

function market_default_image_for_category(string $category): string
{
    $images = [
        'Phones' => 'assets/images/product-phone.jpg',
        'Fashion' => 'assets/images/product-bag.jpg',
        'Homeware' => 'assets/images/product-lamp.jpg',
        'Study' => 'assets/images/product-books.jpg',
    ];

    return $images[$category] ?? 'assets/images/product-lamp.jpg';
}

function market_humanize_delivery_method(string $deliveryMethod): string
{
    $labels = [
        'collection' => 'Collection',
        'standard_delivery' => 'Standard delivery',
        'express_delivery' => 'Express delivery',
    ];

    return $labels[$deliveryMethod] ?? ucfirst(str_replace('_', ' ', $deliveryMethod));
}

function market_humanize_payment_method(string $paymentMethod): string
{
    $labels = [
        'card' => 'Card',
        'eft' => 'EFT',
        'cash' => 'Cash',
    ];

    return $labels[$paymentMethod] ?? strtoupper($paymentMethod);
}

function market_humanize_order_status(string $status): string
{
    $labels = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function market_humanize_payment_status(string $status): string
{
    $labels = [
        'pending' => 'Pending',
        'awaiting_confirmation' => 'Awaiting confirmation',
        'paid' => 'Paid',
        'failed' => 'Failed',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function market_humanize_product_status(string $status): string
{
    $labels = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending_review' => 'Pending review',
        'archived' => 'Archived',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function market_humanize_review_status(string $status): string
{
    $labels = [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function market_role_label(string $role): string
{
    $labels = [
        'buyer' => 'Buyer',
        'seller' => 'Seller',
        'admin' => 'Admin',
    ];

    return $labels[$role] ?? ucfirst($role);
}

function market_simulated_payment_status(string $paymentMethod): string
{
    if ($paymentMethod === 'card') {
        return 'paid';
    }

    if ($paymentMethod === 'eft') {
        return 'awaiting_confirmation';
    }

    return 'pending';
}

function market_simulated_order_status(string $paymentStatus): string
{
    return $paymentStatus === 'paid' ? 'paid' : 'pending';
}

function market_sample_categories(): array
{
    return [
        ['id' => 1, 'name' => 'Phones', 'count' => 1],
        ['id' => 2, 'name' => 'Fashion', 'count' => 1],
        ['id' => 3, 'name' => 'Homeware', 'count' => 1],
        ['id' => 4, 'name' => 'Study', 'count' => 1],
    ];
}

function market_sample_users(): array
{
    return [
        [
            'id' => 1,
            'full_name' => 'Nandi P.',
            'email' => 'buyer@locallink.market',
            'password_hash' => 'pbkdf2_sha256$200000$9Rqg9lnpGIQ4mVGqfIpm0A==$0TcwwWQErN4W8mR3rqXsE2XnTrEBV7KftSOs85uvdPg=',
            'role' => 'buyer',
            'is_admin' => 0,
            'status' => 'active',
            'created_at' => '2026-05-29 08:00:00',
        ],
        [
            'id' => 2,
            'full_name' => 'Admin User',
            'email' => 'admin@locallink.market',
            'password_hash' => 'pbkdf2_sha256$200000$LcEaCRQ6IWNgkoBxxSA0Cg==$4NWQj6ETMe6mm6PZp8hWeUjx9re8YHPaqudT5N9XRD8=',
            'role' => 'admin',
            'is_admin' => 1,
            'status' => 'active',
            'created_at' => '2026-05-29 08:05:00',
        ],
        [
            'id' => 3,
            'full_name' => 'Anele Mokoena',
            'email' => 'seller@locallink.market',
            'password_hash' => 'pbkdf2_sha256$200000$8H3xWtbRQC0m8AK02FsaXA==$BMLy5H34dZq1jDUxqDqSpq9zywh7R4YJkLPrDqYB5aQ=',
            'role' => 'seller',
            'is_admin' => 0,
            'status' => 'active',
            'created_at' => '2026-05-29 08:10:00',
        ],
    ];
}

function market_sample_products(): array
{
    return [
        [
            'id' => 1,
            'seller_id' => 3,
            'seller_name' => 'Anele Mokoena',
            'seller_location' => 'Soweto, Johannesburg',
            'category_id' => 1,
            'title' => 'Refurbished smartphone',
            'description' => 'Unlocked Android phone with charger included.',
            'price' => 2450.00,
            'stock' => 3,
            'status' => 'active',
            'image_path' => 'assets/images/product-phone.jpg',
            'created_at' => '2026-06-01 09:00:00',
            'avg_rating' => 5.0,
            'review_count' => 1,
        ],
        [
            'id' => 2,
            'seller_id' => 3,
            'seller_name' => 'Anele Mokoena',
            'seller_location' => 'Soweto, Johannesburg',
            'category_id' => 2,
            'title' => 'Canvas street backpack',
            'description' => 'Everyday backpack with laptop sleeve and side pockets.',
            'price' => 380.00,
            'stock' => 5,
            'status' => 'active',
            'image_path' => 'assets/images/product-bag.jpg',
            'created_at' => '2026-06-02 11:00:00',
            'avg_rating' => 5.0,
            'review_count' => 1,
        ],
        [
            'id' => 3,
            'seller_id' => 3,
            'seller_name' => 'Anele Mokoena',
            'seller_location' => 'Soweto, Johannesburg',
            'category_id' => 3,
            'title' => 'Minimal desk lamp',
            'description' => 'Compact desk lamp for study rooms and small offices.',
            'price' => 220.00,
            'stock' => 4,
            'status' => 'active',
            'image_path' => 'assets/images/product-lamp.jpg',
            'created_at' => '2026-06-03 10:00:00',
            'avg_rating' => 5.0,
            'review_count' => 1,
        ],
        [
            'id' => 4,
            'seller_id' => 3,
            'seller_name' => 'Anele Mokoena',
            'seller_location' => 'Soweto, Johannesburg',
            'category_id' => 4,
            'title' => 'Accounting textbook set',
            'description' => 'Second-year accounting books in good condition.',
            'price' => 640.00,
            'stock' => 2,
            'status' => 'active',
            'image_path' => 'assets/images/product-books.jpg',
            'created_at' => '2026-06-04 08:30:00',
            'avg_rating' => 5.0,
            'review_count' => 1,
        ],
    ];
}

function market_sample_orders(): array
{
    return [
        [
            'id' => 1,
            'order_number' => 'LLM-1038',
            'user_id' => 1,
            'seller_id' => 3,
            'buyer' => 'Nandi P.',
            'seller' => 'Anele Mokoena',
            'item' => 'Canvas street backpack',
            'quantity' => 1,
            'total_amount' => 425.00,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'delivery_method' => 'standard_delivery',
            'created_at' => '2026-05-29 10:15:00',
            'review_id' => 0,
            'can_review' => false,
        ],
        [
            'id' => 2,
            'order_number' => 'LLM-1031',
            'user_id' => 1,
            'seller_id' => 3,
            'buyer' => 'Nandi P.',
            'seller' => 'Anele Mokoena',
            'item' => 'Minimal desk lamp',
            'quantity' => 1,
            'total_amount' => 220.00,
            'status' => 'completed',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'delivery_method' => 'collection',
            'created_at' => '2026-05-28 09:00:00',
            'review_id' => 1,
            'can_review' => false,
        ],
    ];
}

function market_table_exists(string $table): bool
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return false;
    }

    static $cache = [];
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name'
        );
        $statement->execute(['table_name' => $table]);
        $cache[$table] = (int) $statement->fetchColumn() > 0;
    } catch (Throwable $exception) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

function market_table_has_column(string $table, string $column): bool
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return false;
    }

    static $cache = [];
    $cacheKey = $table . '.' . $column;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    try {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = :table_name
               AND column_name = :column_name'
        );
        $statement->execute([
            'table_name' => $table,
            'column_name' => $column,
        ]);
        $cache[$cacheKey] = (int) $statement->fetchColumn() > 0;
    } catch (Throwable $exception) {
        $cache[$cacheKey] = false;
    }

    return $cache[$cacheKey];
}

function market_users_have_column(string $column): bool
{
    return market_table_has_column('users', $column);
}

function market_orders_have_column(string $column): bool
{
    return market_table_has_column('orders', $column);
}

function market_has_product_images(): bool
{
    return market_table_exists('product_images');
}

function market_has_normalized_orders(): bool
{
    return market_table_exists('order_items')
        && market_table_exists('order_addresses')
        && market_table_exists('order_payments');
}

function market_category_name(int $categoryId): string
{
    foreach (market_sample_categories() as $category) {
        if ((int) $category['id'] === $categoryId) {
            return $category['name'];
        }
    }

    return 'General';
}

function market_normalize_role(array $user): string
{
    $role = strtolower((string) ($user['role'] ?? ''));

    if ($role === '') {
        $role = !empty($user['is_admin']) ? 'admin' : 'buyer';
    }

    if ($role === 'admin') {
        return 'admin';
    }

    if ($role === 'seller') {
        return 'seller';
    }

    return 'buyer';
}

function market_map_product(array $row): array
{
    $category = (string) ($row['category_name'] ?? market_category_name((int) ($row['category_id'] ?? 0)));
    $imagePath = trim((string) ($row['resolved_image_path'] ?? $row['image_path'] ?? ''));
    $stock = (int) ($row['stock'] ?? 0);
    $status = strtolower((string) ($row['status'] ?? 'active'));
    $ratingAverage = round((float) ($row['avg_rating'] ?? 0), 1);
    $ratingCount = (int) ($row['review_count'] ?? 0);

    return [
        'id' => (int) ($row['id'] ?? 0),
        'seller_id' => isset($row['seller_id']) ? (int) $row['seller_id'] : 0,
        'seller_name' => (string) ($row['seller_name'] ?? 'LocalLink Seller'),
        'seller_location' => (string) ($row['seller_location'] ?? ''),
        'category_id' => (int) ($row['category_id'] ?? 0),
        'category' => $category,
        'title' => (string) ($row['title'] ?? ''),
        'description' => (string) ($row['description'] ?? ''),
        'price_amount' => (float) ($row['price'] ?? 0),
        'price' => market_format_money((float) ($row['price'] ?? 0)),
        'stock' => $stock,
        'stock_label' => $stock > 0 ? $stock . ' in stock' : 'Out of stock',
        'status' => $status,
        'status_label' => market_humanize_product_status($status),
        'image' => $imagePath !== '' ? $imagePath : market_default_image_for_category($category),
        'rating_average' => $ratingAverage,
        'rating_count' => $ratingCount,
        'rating_label' => $ratingCount > 0 ? $ratingAverage . ' / 5 from ' . $ratingCount . ' review' . ($ratingCount === 1 ? '' : 's') : 'No reviews yet',
        'created_at' => (string) ($row['created_at'] ?? ''),
        'created_on' => market_format_date((string) ($row['created_at'] ?? '')),
    ];
}

function market_normalize_order_summary(array $row): array
{
    $statusRaw = strtolower((string) ($row['status'] ?? 'pending'));
    $paymentStatusRaw = strtolower((string) ($row['payment_status'] ?? 'pending'));
    $paymentMethodRaw = strtolower((string) ($row['payment_method'] ?? ''));
    $deliveryMethodRaw = strtolower((string) ($row['delivery_method'] ?? 'collection'));

    return [
        'id' => (int) ($row['id'] ?? 0),
        'code' => (string) ($row['order_number'] ?? ''),
        'buyer' => (string) ($row['buyer'] ?? ''),
        'seller' => (string) ($row['seller'] ?? ''),
        'item' => (string) ($row['item'] ?? 'Product'),
        'quantity' => (int) ($row['quantity'] ?? 0),
        'status' => market_humanize_order_status($statusRaw),
        'status_raw' => $statusRaw,
        'payment_status' => market_humanize_payment_status($paymentStatusRaw),
        'payment_status_raw' => $paymentStatusRaw,
        'payment_method' => market_humanize_payment_method($paymentMethodRaw),
        'payment_method_raw' => $paymentMethodRaw,
        'delivery_method' => market_humanize_delivery_method($deliveryMethodRaw),
        'delivery_method_raw' => $deliveryMethodRaw,
        'total' => market_format_money((float) ($row['total_amount'] ?? 0)),
        'raw_total' => (float) ($row['total_amount'] ?? 0),
        'placed_on' => market_format_date((string) ($row['created_at'] ?? '')),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'review_id' => isset($row['review_id']) ? (int) $row['review_id'] : 0,
        'can_review' => !empty($row['can_review']),
    ];
}

function market_bind_list(string $prefix, array $values, array &$params): array
{
    $placeholders = [];
    foreach (array_values($values) as $index => $value) {
        $key = $prefix . $index;
        $placeholders[] = ':' . $key;
        $params[$key] = $value;
    }

    return $placeholders;
}

function market_user_select_columns(): string
{
    $roleColumn = market_users_have_column('role')
        ? 'role'
        : "CASE WHEN is_admin = 1 THEN 'admin' ELSE 'buyer' END AS role";
    $statusColumn = market_users_have_column('status') ? 'status' : "'active' AS status";
    $lastLoginColumn = market_users_have_column('last_login_at') ? 'last_login_at' : 'NULL AS last_login_at';

    return 'id, full_name, email, password_hash, ' . $roleColumn . ', ' . $statusColumn . ', is_admin, ' . $lastLoginColumn . ', created_at';
}

function market_normalize_user(array $user): array
{
    $role = market_normalize_role($user);
    $user['role'] = $role;
    $user['is_admin'] = $role === 'admin' ? 1 : 0;
    $user['status'] = (string) ($user['status'] ?? 'active');

    return $user;
}

function market_find_user_by_email(string $email): ?array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        foreach (market_sample_users() as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return market_normalize_user($user);
            }
        }

        return null;
    }

    $statement = $pdo->prepare(
        'SELECT ' . market_user_select_columns() . '
         FROM users
         WHERE LOWER(email) = LOWER(:email)
         LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    return $user ? market_normalize_user($user) : null;
}

function market_get_user_by_id(int $userId): ?array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        foreach (market_sample_users() as $user) {
            if ((int) $user['id'] === $userId) {
                return market_normalize_user($user);
            }
        }

        return null;
    }

    try {
        $statement = $pdo->prepare(
            'SELECT ' . market_user_select_columns() . '
             FROM users
             WHERE id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $user = $statement->fetch();

        return $user ? market_normalize_user($user) : null;
    } catch (Throwable $exception) {
        return null;
    }
}

function market_get_categories(): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return market_sample_categories();
    }

    try {
        $statement = $pdo->query(
            'SELECT c.id,
                    c.name,
                    SUM(CASE WHEN p.status = "active" THEN 1 ELSE 0 END) AS item_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.name ASC'
        );

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'count' => (int) ($row['item_count'] ?? 0),
            ];
        }, $statement->fetchAll());
    } catch (Throwable $exception) {
        return market_sample_categories();
    }
}

function market_get_products(array $filters = []): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $products = market_sample_products();
        if (!empty($filters['seller_id'])) {
            $products = array_values(array_filter($products, static function (array $product) use ($filters): bool {
                return (int) ($product['seller_id'] ?? 0) === (int) $filters['seller_id'];
            }));
        }

        return array_map('market_map_product', $products);
    }

    try {
        $conditions = ['1 = 1'];
        $params = [];
        $statuses = null;

        if (isset($filters['status'])) {
            $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
        } elseif (empty($filters['include_non_public'])) {
            $statuses = ['active'];
        }

        if ($statuses !== null && $statuses !== []) {
            $placeholders = market_bind_list('product_status_', array_map('strval', $statuses), $params);
            $conditions[] = 'p.status IN (' . implode(', ', $placeholders) . ')';
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(p.title LIKE :search OR p.description LIKE :search OR c.name LIKE :search OR u.full_name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $categoryId = max(0, (int) ($filters['category_id'] ?? 0));
        if ($categoryId > 0) {
            $conditions[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sellerId = max(0, (int) ($filters['seller_id'] ?? 0));
        if ($sellerId > 0) {
            $conditions[] = 'p.seller_id = :seller_id';
            $params['seller_id'] = $sellerId;
        }

        $orderBy = ($filters['sort'] ?? 'newest') === 'price_low'
            ? 'p.price ASC, p.created_at DESC'
            : 'p.created_at DESC, p.id DESC';

        $limitSql = isset($filters['limit']) ? ' LIMIT ' . max(1, (int) $filters['limit']) : '';
        $imageJoin = market_has_product_images() ? 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1' : '';
        $imageSelect = market_has_product_images()
            ? 'COALESCE(pi.image_path, p.image_path) AS resolved_image_path'
            : 'p.image_path AS resolved_image_path';

        $sql = 'SELECT p.id,
                       p.seller_id,
                       p.category_id,
                       p.title,
                       p.description,
                       p.price,
                       p.stock,
                       p.status,
                       p.image_path,
                       p.created_at,
                       ' . $imageSelect . ',
                       c.name AS category_name,
                       COALESCE(u.full_name, "LocalLink Seller") AS seller_name,
                       COALESCE(sp.location, "") AS seller_location,
                       COALESCE(rv.avg_rating, 0) AS avg_rating,
                       COALESCE(rv.review_count, 0) AS review_count
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                LEFT JOIN users u ON u.id = p.seller_id
                LEFT JOIN seller_profiles sp ON sp.user_id = p.seller_id
                LEFT JOIN (
                    SELECT seller_id,
                           ROUND(AVG(rating), 1) AS avg_rating,
                           COUNT(*) AS review_count
                    FROM reviews
                    WHERE status = "visible"
                    GROUP BY seller_id
                ) rv ON rv.seller_id = p.seller_id
                ' . $imageJoin . '
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY ' . $orderBy . $limitSql;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return array_map('market_map_product', $statement->fetchAll());
    } catch (Throwable $exception) {
        return array_map('market_map_product', market_sample_products());
    }
}

function market_get_product_by_id(int $productId, bool $allowNonPublic = false): ?array
{
    $products = market_get_products([
        'include_non_public' => $allowNonPublic,
        'status' => $allowNonPublic ? ['active', 'inactive', 'pending_review', 'archived'] : ['active'],
    ]);

    foreach ($products as $product) {
        if ((int) $product['id'] === $productId) {
            return $product;
        }
    }

    return null;
}

function market_create_user(string $fullName, string $email, string $password, string $role = 'buyer'): int
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    if (market_find_user_by_email($email) !== null) {
        throw new RuntimeException('An account with that email address already exists.');
    }

    $normalizedRole = in_array($role, ['buyer', 'seller', 'admin'], true) ? $role : 'buyer';
    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, role, status, is_admin)
         VALUES (:full_name, :email, :password_hash, :role, :status, :is_admin)'
    );
    $statement->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => app_hash_password($password),
        'role' => $normalizedRole,
        'status' => 'active',
        'is_admin' => $normalizedRole === 'admin' ? 1 : 0,
    ]);

    return (int) $pdo->lastInsertId();
}

function market_authenticate_user(string $email, string $password): ?array
{
    $user = market_find_user_by_email($email);

    if ($user === null) {
        return null;
    }

    if (!app_verify_password($password, $user['password_hash'])) {
        return null;
    }

    if (!app_user_can_login($user)) {
        throw new RuntimeException('This account is disabled. Contact the store administrator.');
    }

    market_record_user_login((int) $user['id']);

    return $user;
}

function market_record_user_login(int $userId): void
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return;
    }

    try {
        if (market_users_have_column('last_login_at')) {
            $statement = $pdo->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :user_id');
            $statement->execute(['user_id' => $userId]);
        }

        if (market_table_exists('user_login_audit')) {
            $audit = $pdo->prepare(
                'INSERT INTO user_login_audit (user_id, ip_address, user_agent)
                 VALUES (:user_id, :ip_address, :user_agent)'
            );
            $audit->execute([
                'user_id' => $userId,
                'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]);
        }
    } catch (Throwable $exception) {
        return;
    }
}

function market_create_or_replace_primary_image(PDO $pdo, int $productId, string $imagePath): void
{
    if (!market_has_product_images()) {
        return;
    }

    $update = $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id');
    $update->execute(['product_id' => $productId]);

    $existing = $pdo->prepare('SELECT id FROM product_images WHERE product_id = :product_id ORDER BY id ASC LIMIT 1');
    $existing->execute(['product_id' => $productId]);
    $imageId = $existing->fetchColumn();

    if ($imageId !== false) {
        $statement = $pdo->prepare(
            'UPDATE product_images
             SET image_path = :image_path, is_primary = 1, sort_order = 1
             WHERE id = :image_id'
        );
        $statement->execute([
            'image_path' => $imagePath,
            'image_id' => $imageId,
        ]);
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
         VALUES (:product_id, :image_path, 1, 1)'
    );
    $statement->execute([
        'product_id' => $productId,
        'image_path' => $imagePath,
    ]);
}

function market_create_product(array $input): int
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    $categoryId = max(0, (int) ($input['category_id'] ?? 0));
    $statement = $pdo->prepare('SELECT name FROM categories WHERE id = :category_id LIMIT 1');
    $statement->execute(['category_id' => $categoryId]);
    $categoryName = $statement->fetchColumn();

    if ($categoryName === false) {
        throw new RuntimeException('Choose a valid category.');
    }

    $sellerId = max(0, (int) ($input['seller_id'] ?? 0));
    $imagePath = trim((string) ($input['image_path'] ?? market_default_image_for_category((string) $categoryName)));
    $status = strtolower((string) ($input['status'] ?? ($sellerId > 0 ? 'pending_review' : 'active')));
    if (!in_array($status, ['active', 'inactive', 'pending_review', 'archived'], true)) {
        $status = $sellerId > 0 ? 'pending_review' : 'active';
    }

    $insert = $pdo->prepare(
        'INSERT INTO products (seller_id, category_id, title, description, price, stock, status, image_path)
         VALUES (:seller_id, :category_id, :title, :description, :price, :stock, :status, :image_path)'
    );
    $insert->execute([
        'seller_id' => $sellerId > 0 ? $sellerId : null,
        'category_id' => $categoryId,
        'title' => trim((string) ($input['title'] ?? '')),
        'description' => trim((string) ($input['description'] ?? '')),
        'price' => (float) ($input['price'] ?? 0),
        'stock' => max(0, (int) ($input['stock'] ?? 0)),
        'status' => $status,
        'image_path' => $imagePath,
    ]);

    $productId = (int) $pdo->lastInsertId();
    market_create_or_replace_primary_image($pdo, $productId, $imagePath);

    return $productId;
}

function market_update_product(int $productId, array $input): void
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    $existing = market_get_product_by_id($productId, true);
    if ($existing === null) {
        throw new RuntimeException('That product could not be found.');
    }

    $params = ['product_id' => $productId];
    $sets = [];

    if (array_key_exists('seller_id', $input)) {
        $sellerId = max(0, (int) $input['seller_id']);
        $sets[] = 'seller_id = :seller_id';
        $params['seller_id'] = $sellerId > 0 ? $sellerId : null;
    }

    if (array_key_exists('category_id', $input)) {
        $categoryId = max(0, (int) $input['category_id']);
        $categoryLookup = $pdo->prepare('SELECT name FROM categories WHERE id = :category_id LIMIT 1');
        $categoryLookup->execute(['category_id' => $categoryId]);
        if ($categoryLookup->fetchColumn() === false) {
            throw new RuntimeException('Choose a valid category.');
        }
        $sets[] = 'category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    foreach (['title', 'description', 'image_path'] as $textField) {
        if (array_key_exists($textField, $input)) {
            $sets[] = $textField . ' = :' . $textField;
            $params[$textField] = trim((string) $input[$textField]);
        }
    }

    foreach (['price', 'stock'] as $numericField) {
        if (array_key_exists($numericField, $input)) {
            $sets[] = $numericField . ' = :' . $numericField;
            $params[$numericField] = $numericField === 'price'
                ? (float) $input[$numericField]
                : max(0, (int) $input[$numericField]);
        }
    }

    if (array_key_exists('status', $input)) {
        $status = strtolower((string) $input['status']);
        if (!in_array($status, ['active', 'inactive', 'pending_review', 'archived'], true)) {
            throw new RuntimeException('Choose a valid product status.');
        }
        $sets[] = 'status = :status';
        $params['status'] = $status;
    }

    if ($sets === []) {
        return;
    }

    $statement = $pdo->prepare('UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = :product_id');
    $statement->execute($params);

    if (array_key_exists('image_path', $input)) {
        market_create_or_replace_primary_image($pdo, $productId, trim((string) $input['image_path']));
    }
}

function market_delivery_fee(string $deliveryMethod): float
{
    $fees = [
        'collection' => 0.00,
        'standard_delivery' => 45.00,
        'express_delivery' => 85.00,
    ];

    return $fees[$deliveryMethod] ?? $fees['standard_delivery'];
}

function market_generate_order_number(PDO $pdo): string
{
    do {
        $value = 'LLM-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $statement = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE order_number = :order_number');
        $statement->execute(['order_number' => $value]);
    } while ((int) $statement->fetchColumn() > 0);

    return $value;
}

function market_generate_payment_reference(string $paymentMethod): string
{
    return 'SIM-' . strtoupper($paymentMethod) . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
}

function market_create_order(array $input): string
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    $userId = (int) ($input['user_id'] ?? 0);
    $productId = (int) ($input['product_id'] ?? 0);
    $quantity = max(1, (int) ($input['quantity'] ?? 1));
    $deliveryMethod = (string) ($input['delivery_method'] ?? 'standard_delivery');
    $paymentMethod = (string) ($input['payment_method'] ?? 'card');
    $buyerNote = trim((string) ($input['buyer_note'] ?? ''));

    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'SELECT id, seller_id, price, stock, status
             FROM products
             WHERE id = :product_id
             FOR UPDATE'
        );
        $statement->execute(['product_id' => $productId]);
        $product = $statement->fetch();

        if (!$product) {
            throw new RuntimeException('That product could not be loaded.');
        }

        if ((string) ($product['status'] ?? 'active') !== 'active') {
            throw new RuntimeException('That product is not currently available for checkout.');
        }

        if ((int) $product['stock'] < $quantity) {
            throw new RuntimeException('Not enough stock is available for that quantity.');
        }

        $orderNumber = market_generate_order_number($pdo);
        $unitPrice = (float) $product['price'];
        $subtotalAmount = $unitPrice * $quantity;
        $deliveryFee = market_delivery_fee($deliveryMethod);
        $totalAmount = $subtotalAmount + $deliveryFee;
        $paymentStatus = market_simulated_payment_status($paymentMethod);
        $orderStatus = market_simulated_order_status($paymentStatus);
        $sellerId = isset($product['seller_id']) ? (int) $product['seller_id'] : 0;

        $orderInsert = $pdo->prepare(
            'INSERT INTO orders (order_number, user_id, seller_id, status, payment_status, subtotal_amount, delivery_fee, total_amount, delivery_method, buyer_note)
             VALUES (:order_number, :user_id, :seller_id, :status, :payment_status, :subtotal_amount, :delivery_fee, :total_amount, :delivery_method, :buyer_note)'
        );
        $orderInsert->execute([
            'order_number' => $orderNumber,
            'user_id' => $userId,
            'seller_id' => $sellerId > 0 ? $sellerId : null,
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'subtotal_amount' => $subtotalAmount,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'delivery_method' => $deliveryMethod,
            'buyer_note' => $buyerNote,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $itemInsert = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
             VALUES (:order_id, :product_id, :quantity, :unit_price, :line_total)'
        );
        $itemInsert->execute([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $subtotalAmount,
        ]);

        if (market_table_exists('order_addresses')) {
            $addressInsert = $pdo->prepare(
                'INSERT INTO order_addresses (order_id, contact_name, phone_number, address_line_1, address_line_2, city, postal_code, collection_note)
                 VALUES (:order_id, :contact_name, :phone_number, :address_line_1, :address_line_2, :city, :postal_code, :collection_note)'
            );
            $addressInsert->execute([
                'order_id' => $orderId,
                'contact_name' => trim((string) ($input['contact_name'] ?? '')),
                'phone_number' => trim((string) ($input['phone_number'] ?? '')),
                'address_line_1' => trim((string) ($input['address_line_1'] ?? '')),
                'address_line_2' => trim((string) ($input['address_line_2'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'postal_code' => trim((string) ($input['postal_code'] ?? '')),
                'collection_note' => $deliveryMethod === 'collection' ? $buyerNote : null,
            ]);
        }

        if (market_table_exists('order_payments')) {
            $paymentInsert = $pdo->prepare(
                'INSERT INTO order_payments (order_id, payment_method, payment_status, provider_reference, paid_at)
                 VALUES (:order_id, :payment_method, :payment_status, :provider_reference, :paid_at)'
            );
            $paymentInsert->execute([
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'provider_reference' => market_generate_payment_reference($paymentMethod),
                'paid_at' => $paymentStatus === 'paid' ? date('Y-m-d H:i:s') : null,
            ]);
        }

        $updateStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :product_id');
        $updateStock->execute([
            'quantity' => $quantity,
            'product_id' => $productId,
        ]);

        $pdo->commit();

        return $orderNumber;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function market_fetch_order_summaries(array $filters = [], int $limit = 0): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $orders = market_sample_orders();

        if (!empty($filters['user_id'])) {
            $orders = array_values(array_filter($orders, static function (array $order) use ($filters): bool {
                return (int) ($order['user_id'] ?? 0) === (int) $filters['user_id'];
            }));
        }

        if (!empty($filters['seller_id'])) {
            $orders = array_values(array_filter($orders, static function (array $order) use ($filters): bool {
                return (int) ($order['seller_id'] ?? 0) === (int) $filters['seller_id'];
            }));
        }

        usort($orders, static function (array $left, array $right): int {
            return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
        });

        if ($limit > 0) {
            $orders = array_slice($orders, 0, $limit);
        }

        return array_map('market_normalize_order_summary', $orders);
    }

    try {
        $conditions = ['1 = 1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $conditions[] = 'o.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['seller_id'])) {
            $conditions[] = 'o.seller_id = :seller_id';
            $params['seller_id'] = (int) $filters['seller_id'];
        }

        if (!empty($filters['order_id'])) {
            $conditions[] = 'o.id = :order_id';
            $params['order_id'] = (int) $filters['order_id'];
        }

        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $placeholders = market_bind_list('order_status_', array_map('strval', $statuses), $params);
            $conditions[] = 'o.status IN (' . implode(', ', $placeholders) . ')';
        }

        $limitSql = $limit > 0 ? ' LIMIT ' . max(1, $limit) : '';

        $sql = 'SELECT o.id,
                       o.order_number,
                       buyer.full_name AS buyer,
                       COALESCE(seller.full_name, "Unassigned seller") AS seller,
                       COALESCE(GROUP_CONCAT(p.title ORDER BY oi.id SEPARATOR ", "), "Product") AS item,
                       COALESCE(SUM(oi.quantity), 0) AS quantity,
                       o.status,
                       o.payment_status,
                       COALESCE(MAX(op.payment_method), "") AS payment_method,
                       o.delivery_method,
                       o.total_amount,
                       o.created_at,
                       MAX(r.id) AS review_id,
                       MAX(CASE WHEN r.id IS NULL AND o.status = "completed" THEN 1 ELSE 0 END) AS can_review
                FROM orders o
                INNER JOIN users buyer ON buyer.id = o.user_id
                LEFT JOIN users seller ON seller.id = o.seller_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN products p ON p.id = oi.product_id
                LEFT JOIN order_payments op ON op.order_id = o.id
                LEFT JOIN reviews r ON r.order_id = o.id
                WHERE ' . implode(' AND ', $conditions) . '
                GROUP BY o.id, o.order_number, buyer.full_name, seller.full_name, o.status, o.payment_status, o.delivery_method, o.total_amount, o.created_at
                ORDER BY o.created_at DESC' . $limitSql;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return array_map('market_normalize_order_summary', $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}

function market_get_buyer_orders(int $userId): array
{
    return market_fetch_order_summaries(['user_id' => $userId]);
}

function market_get_buyer_stats(int $userId): array
{
    $orders = market_get_buyer_orders($userId);
    $openOrders = 0;
    $totalSpent = 0.0;

    foreach ($orders as $order) {
        if (in_array($order['status_raw'], ['pending', 'paid', 'processing', 'ready'], true)) {
            $openOrders++;
        }

        $totalSpent += (float) $order['raw_total'];
    }

    return [
        ['label' => 'Orders', 'value' => (string) count($orders)],
        ['label' => 'Open orders', 'value' => (string) $openOrders],
        ['label' => 'Total spent', 'value' => market_format_money($totalSpent)],
    ];
}

function market_get_admin_stats(): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $orders = market_sample_orders();
        $revenue = 0.0;
        foreach ($orders as $order) {
            $revenue += (float) ($order['total_amount'] ?? 0);
        }

        return [
            ['label' => 'Users', 'value' => (string) count(market_sample_users())],
            ['label' => 'Sellers', 'value' => '1'],
            ['label' => 'Products', 'value' => (string) count(market_sample_products())],
            ['label' => 'Orders', 'value' => (string) count($orders)],
            ['label' => 'Revenue', 'value' => market_format_money($revenue)],
        ];
    }

    try {
        $statement = $pdo->query(
            'SELECT
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COUNT(*) FROM users WHERE role = "seller") AS total_sellers,
                (SELECT COUNT(*) FROM products) AS total_products,
                (SELECT COUNT(*) FROM orders) AS total_orders,
                (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> "cancelled") AS total_revenue'
        );
        $row = $statement->fetch();

        return [
            ['label' => 'Users', 'value' => (string) ((int) ($row['total_users'] ?? 0))],
            ['label' => 'Sellers', 'value' => (string) ((int) ($row['total_sellers'] ?? 0))],
            ['label' => 'Products', 'value' => (string) ((int) ($row['total_products'] ?? 0))],
            ['label' => 'Orders', 'value' => (string) ((int) ($row['total_orders'] ?? 0))],
            ['label' => 'Revenue', 'value' => market_format_money((float) ($row['total_revenue'] ?? 0))],
        ];
    } catch (Throwable $exception) {
        return [
            ['label' => 'Users', 'value' => '0'],
            ['label' => 'Sellers', 'value' => '0'],
            ['label' => 'Products', 'value' => '0'],
            ['label' => 'Orders', 'value' => '0'],
            ['label' => 'Revenue', 'value' => market_format_money(0)],
        ];
    }
}

function market_get_admin_recent_orders(int $limit = 5): array
{
    return market_fetch_order_summaries([], max(1, $limit));
}

function market_get_admin_recent_users(int $limit = 5): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $users = market_sample_users();
        usort($users, static function (array $left, array $right): int {
            return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
        });

        return array_map(static function (array $user): array {
            $user = market_normalize_user($user);

            return [
                'id' => (int) $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => market_role_label($user['role']),
                'status' => ucfirst($user['status']),
                'joined' => market_format_date((string) ($user['created_at'] ?? '')),
            ];
        }, array_slice($users, 0, max(1, $limit)));
    }

    try {
        $statement = $pdo->query(
            'SELECT ' . market_user_select_columns() . '
             FROM users
             ORDER BY created_at DESC
             LIMIT ' . max(1, $limit)
        );

        return array_map(static function (array $row): array {
            $row = market_normalize_user($row);

            return [
                'id' => (int) $row['id'],
                'name' => $row['full_name'],
                'email' => $row['email'],
                'role' => market_role_label($row['role']),
                'status' => ucfirst((string) $row['status']),
                'joined' => market_format_date((string) ($row['created_at'] ?? '')),
            ];
        }, $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}

function market_get_seller_stats(?int $sellerId = null): array
{
    $products = market_get_seller_products($sellerId, 200);
    $orders = market_get_seller_orders($sellerId, 200);
    $stockUnits = 0;
    $pendingOrders = 0;
    $revenue = 0.0;

    foreach ($products as $product) {
        $stockUnits += (int) ($product['stock'] ?? 0);
    }

    foreach ($orders as $order) {
        if (in_array($order['status_raw'], ['pending', 'paid', 'processing', 'ready'], true)) {
            $pendingOrders++;
        }
        if ($order['status_raw'] !== 'cancelled') {
            $revenue += (float) ($order['raw_total'] ?? 0);
        }
    }

    return [
        ['label' => 'Listings', 'value' => (string) count($products)],
        ['label' => 'Stock units', 'value' => (string) $stockUnits],
        ['label' => 'Received orders', 'value' => (string) count($orders)],
        ['label' => 'Open orders', 'value' => (string) $pendingOrders],
        ['label' => 'Revenue', 'value' => market_format_money($revenue)],
    ];
}

function market_get_seller_products(?int $sellerId = null, int $limit = 6): array
{
    $filters = [
        'limit' => max(1, $limit),
        'include_non_public' => true,
    ];

    if ($sellerId !== null && $sellerId > 0) {
        $filters['seller_id'] = $sellerId;
    }

    return market_get_products($filters);
}

function market_get_seller_orders(?int $sellerId = null, int $limit = 6): array
{
    if ($sellerId !== null && $sellerId > 0) {
        return market_fetch_order_summaries(['seller_id' => $sellerId], max(1, $limit));
    }

    return market_fetch_order_summaries([], max(1, $limit));
}


