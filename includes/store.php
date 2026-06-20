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
        'Phones' => 'assets/images/product-phone.svg',
        'Fashion' => 'assets/images/product-bag.svg',
        'Homeware' => 'assets/images/product-lamp.svg',
        'Study' => 'assets/images/product-books.svg',
    ];

    return $images[$category] ?? 'assets/images/product-lamp.svg';
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
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    return $labels[$status] ?? ucfirst($status);
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
    ];
}

function market_sample_products(): array
{
    return [
        [
            'id' => 1,
            'category_id' => 1,
            'title' => 'Refurbished smartphone',
            'description' => 'Unlocked Android phone with charger included.',
            'price' => 2450.00,
            'stock' => 3,
            'image_path' => 'assets/images/product-phone.svg',
            'created_at' => '2026-06-01 09:00:00',
        ],
        [
            'id' => 2,
            'category_id' => 2,
            'title' => 'Canvas street backpack',
            'description' => 'Everyday backpack with laptop sleeve and side pockets.',
            'price' => 380.00,
            'stock' => 5,
            'image_path' => 'assets/images/product-bag.svg',
            'created_at' => '2026-06-02 11:00:00',
        ],
        [
            'id' => 3,
            'category_id' => 3,
            'title' => 'Minimal desk lamp',
            'description' => 'Compact desk lamp for study rooms and small offices.',
            'price' => 220.00,
            'stock' => 4,
            'image_path' => 'assets/images/product-lamp.svg',
            'created_at' => '2026-06-03 10:00:00',
        ],
        [
            'id' => 4,
            'category_id' => 4,
            'title' => 'Accounting textbook set',
            'description' => 'Second-year accounting books in good condition.',
            'price' => 640.00,
            'stock' => 2,
            'image_path' => 'assets/images/product-books.svg',
            'created_at' => '2026-06-04 08:30:00',
        ],
    ];
}

function market_sample_orders(): array
{
    return [
        [
            'order_number' => 'LLM-1038',
            'user_id' => 1,
            'buyer' => 'Nandi P.',
            'item' => 'Canvas street backpack',
            'quantity' => 1,
            'total_amount' => 425.00,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'delivery_method' => 'standard_delivery',
            'created_at' => '2026-05-29 10:15:00',
        ],
        [
            'order_number' => 'LLM-1031',
            'user_id' => 1,
            'buyer' => 'Nandi P.',
            'item' => 'Minimal desk lamp',
            'quantity' => 1,
            'total_amount' => 220.00,
            'status' => 'completed',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'delivery_method' => 'collection',
            'created_at' => '2026-05-28 09:00:00',
        ],
    ];
}

function market_table_exists(string $table): bool
{
    static $tables = [];
    $key = strtolower($table);

    if (array_key_exists($key, $tables)) {
        return $tables[$key];
    }

    $pdo = db_try_get_connection();
    if (!$pdo) {
        $tables[$key] = false;
        return false;
    }

    try {
        $statement = $pdo->prepare('SHOW TABLES LIKE :table_name');
        $statement->execute(['table_name' => $table]);
        $tables[$key] = $statement->fetchColumn() !== false;
    } catch (Throwable $exception) {
        $tables[$key] = false;
    }

    return $tables[$key];
}

function market_table_has_column(string $table, string $column): bool
{
    static $columns = [];
    $tableKey = strtolower($table);
    $columnKey = strtolower($column);

    if (!isset($columns[$tableKey])) {
        $columns[$tableKey] = [];
        $pdo = db_try_get_connection();

        if (!$pdo) {
            return false;
        }

        $safeTable = preg_replace('/[^a-z0-9_]+/i', '', $table);

        try {
            $statement = $pdo->query('SHOW COLUMNS FROM `' . $safeTable . '`');
            foreach ($statement->fetchAll() as $row) {
                $columns[$tableKey][strtolower((string) $row['Field'])] = true;
            }
        } catch (Throwable $exception) {
            $columns[$tableKey] = [];
        }
    }

    return isset($columns[$tableKey][$columnKey]);
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
        && market_orders_have_column('subtotal_amount')
        && market_orders_have_column('delivery_fee')
        && market_orders_have_column('payment_status');
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

function market_map_product(array $row): array
{
    $category = $row['category_name'] ?? market_category_name((int) $row['category_id']);
    $stock = (int) ($row['stock'] ?? 0);
    $imagePath = trim((string) ($row['resolved_image_path'] ?? $row['image_path'] ?? ''));

    return [
        'id' => (int) $row['id'],
        'category_id' => (int) $row['category_id'],
        'category' => $category,
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'price_amount' => (float) $row['price'],
        'price' => market_format_money((float) $row['price']),
        'stock' => $stock,
        'stock_label' => $stock > 0 ? $stock . ' in stock' : 'Sold out',
        'image' => $imagePath !== '' ? $imagePath : market_default_image_for_category($category),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function market_normalize_order_summary(array $row): array
{
    return [
        'code' => '#' . (string) $row['order_number'],
        'buyer' => (string) ($row['buyer'] ?? ''),
        'item' => (string) ($row['item'] ?? 'Product'),
        'quantity' => (int) ($row['quantity'] ?? 0),
        'status' => market_humanize_order_status((string) ($row['status'] ?? 'pending')),
        'payment_status' => market_humanize_payment_status((string) ($row['payment_status'] ?? market_simulated_payment_status((string) ($row['payment_method'] ?? 'eft')))),
        'payment_method' => market_humanize_payment_method((string) ($row['payment_method'] ?? 'eft')),
        'delivery_method' => market_humanize_delivery_method((string) ($row['delivery_method'] ?? 'standard_delivery')),
        'total' => market_format_money((float) ($row['total_amount'] ?? 0)),
        'placed_on' => market_format_date((string) ($row['created_at'] ?? '')),
    ];
}

function market_filter_products(array $rows, array $filters = []): array
{
    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $categoryId = max(0, (int) ($filters['category_id'] ?? 0));
    $sort = (string) ($filters['sort'] ?? 'newest');
    $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 0;

    $rows = array_values(array_filter($rows, static function (array $row) use ($search, $categoryId): bool {
        if ($categoryId > 0 && (int) $row['category_id'] !== $categoryId) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $category = strtolower(market_category_name((int) $row['category_id']));
        $haystack = strtolower($row['title'] . ' ' . $row['description'] . ' ' . $category);

        return strpos($haystack, $search) !== false;
    }));

    usort($rows, static function (array $left, array $right) use ($sort): int {
        if ($sort === 'price_low') {
            return (float) $left['price'] <=> (float) $right['price'];
        }

        return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
    });

    if ($limit > 0) {
        $rows = array_slice($rows, 0, $limit);
    }

    return array_map('market_map_product', $rows);
}

function market_get_categories(): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return market_sample_categories();
    }

    try {
        $statement = $pdo->query(
            'SELECT c.id, c.name, COUNT(p.id) AS item_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.name ASC'
        );

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'count' => (int) $row['item_count'],
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
        return market_filter_products(market_sample_products(), $filters);
    }

    try {
        $conditions = ['1 = 1'];
        $params = [];

        if (market_table_has_column('products', 'status')) {
            $conditions[] = "p.status = 'active'";
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(p.title LIKE :search OR p.description LIKE :search OR c.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $categoryId = max(0, (int) ($filters['category_id'] ?? 0));
        if ($categoryId > 0) {
            $conditions[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $orderBy = ($filters['sort'] ?? 'newest') === 'price_low'
            ? 'p.price ASC, p.created_at DESC'
            : 'p.created_at DESC, p.id DESC';

        $limitSql = isset($filters['limit']) ? ' LIMIT ' . max(1, (int) $filters['limit']) : '';
        $imageJoin = market_has_product_images() ? 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1' : '';
        $imageSelect = market_has_product_images()
            ? 'COALESCE(pi.image_path, p.image_path) AS resolved_image_path'
            : 'p.image_path AS resolved_image_path';

        $sql = 'SELECT p.id, p.category_id, p.title, p.description, p.price, p.stock, p.image_path, p.created_at, ' . $imageSelect . ', c.name AS category_name
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                ' . $imageJoin . '
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY ' . $orderBy . $limitSql;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return array_map('market_map_product', $statement->fetchAll());
    } catch (Throwable $exception) {
        return market_filter_products(market_sample_products(), $filters);
    }
}

function market_get_product_by_id(int $productId): ?array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        foreach (market_sample_products() as $row) {
            if ((int) $row['id'] === $productId) {
                return market_map_product($row);
            }
        }

        return null;
    }

    try {
        $imageJoin = market_has_product_images() ? 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1' : '';
        $imageSelect = market_has_product_images()
            ? 'COALESCE(pi.image_path, p.image_path) AS resolved_image_path'
            : 'p.image_path AS resolved_image_path';

        $statement = $pdo->prepare(
            'SELECT p.id, p.category_id, p.title, p.description, p.price, p.stock, p.image_path, p.created_at, ' . $imageSelect . ', c.name AS category_name
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             ' . $imageJoin . '
             WHERE p.id = :product_id
             LIMIT 1'
        );
        $statement->execute(['product_id' => $productId]);
        $row = $statement->fetch();

        return $row ? market_map_product($row) : null;
    } catch (Throwable $exception) {
        return null;
    }
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

function market_user_select_columns(): string
{
    $roleColumn = market_users_have_column('role')
        ? 'role'
        : "CASE WHEN is_admin = 1 THEN 'admin' ELSE 'buyer' END AS role";
    $statusColumn = market_users_have_column('status') ? 'status' : "'active' AS status";

    return 'id, full_name, email, password_hash, ' . $roleColumn . ', ' . $statusColumn . ', is_admin, created_at';
}

function market_normalize_user(array $user): array
{
    $role = strtolower((string) ($user['role'] ?? ''));
    if ($role === '') {
        $role = !empty($user['is_admin']) ? 'admin' : 'buyer';
    }

    $user['role'] = $role === 'admin' ? 'admin' : 'buyer';
    $user['is_admin'] = $user['role'] === 'admin' ? 1 : 0;
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

function market_create_user(string $fullName, string $email, string $password): int
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    if (market_find_user_by_email($email) !== null) {
        throw new RuntimeException('An account with that email address already exists.');
    }

    $columns = ['full_name', 'email', 'password_hash', 'is_admin'];
    $values = [':full_name', ':email', ':password_hash', '0'];
    $params = [
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => app_hash_password($password),
    ];

    if (market_users_have_column('role')) {
        $columns[] = 'role';
        $values[] = ':role';
        $params['role'] = 'buyer';
    }

    if (market_users_have_column('status')) {
        $columns[] = 'status';
        $values[] = ':status';
        $params['status'] = 'active';
    }

    $statement = $pdo->prepare(
        'INSERT INTO users (' . implode(', ', $columns) . ')
         VALUES (' . implode(', ', $values) . ')'
    );
    $statement->execute($params);

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

        $audit = $pdo->prepare(
            'INSERT INTO user_login_audit (user_id, ip_address, user_agent)
             VALUES (:user_id, :ip_address, :user_agent)'
        );
        $audit->execute([
            'user_id' => $userId,
            'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    } catch (Throwable $exception) {
        return;
    }
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

    $defaultImage = market_default_image_for_category((string) $categoryName);
    $columns = ['category_id', 'title', 'description', 'price', 'stock', 'image_path'];
    $values = [':category_id', ':title', ':description', ':price', ':stock', ':image_path'];
    $params = [
        'category_id' => $categoryId,
        'title' => trim((string) $input['title']),
        'description' => trim((string) $input['description']),
        'price' => (float) $input['price'],
        'stock' => max(0, (int) ($input['stock'] ?? 0)),
        'image_path' => $defaultImage,
    ];

    if (market_table_has_column('products', 'status')) {
        $columns[] = 'status';
        $values[] = ':status';
        $params['status'] = 'active';
    }

    $insert = $pdo->prepare(
        'INSERT INTO products (' . implode(', ', $columns) . ')
         VALUES (' . implode(', ', $values) . ')'
    );
    $insert->execute($params);

    $productId = (int) $pdo->lastInsertId();

    if (market_has_product_images()) {
        $imageInsert = $pdo->prepare(
            'INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
             VALUES (:product_id, :image_path, 1, 1)'
        );
        $imageInsert->execute([
            'product_id' => $productId,
            'image_path' => $defaultImage,
        ]);
    }

    return $productId;
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
            'SELECT id, price, stock
             FROM products
             WHERE id = :product_id
             FOR UPDATE'
        );
        $statement->execute(['product_id' => $productId]);
        $product = $statement->fetch();

        if (!$product) {
            throw new RuntimeException('That product could not be loaded.');
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

        if (market_has_normalized_orders()) {
            $orderInsert = $pdo->prepare(
                'INSERT INTO orders (order_number, user_id, status, payment_status, subtotal_amount, delivery_fee, total_amount, delivery_method, buyer_note)
                 VALUES (:order_number, :user_id, :status, :payment_status, :subtotal_amount, :delivery_fee, :total_amount, :delivery_method, :buyer_note)'
            );
            $orderInsert->execute([
                'order_number' => $orderNumber,
                'user_id' => $userId,
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
        } else {
            $orderInsert = $pdo->prepare(
                'INSERT INTO orders (order_number, user_id, product_id, quantity, total_amount, status, delivery_method, payment_method, buyer_note)
                 VALUES (:order_number, :user_id, :product_id, :quantity, :total_amount, :status, :delivery_method, :payment_method, :buyer_note)'
            );
            $orderInsert->execute([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
                'status' => $orderStatus,
                'delivery_method' => $deliveryMethod,
                'payment_method' => $paymentMethod,
                'buyer_note' => $buyerNote,
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
function market_get_buyer_orders(int $userId): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $orders = array_values(array_filter(market_sample_orders(), static function (array $order) use ($userId): bool {
            return (int) $order['user_id'] === $userId;
        }));

        usort($orders, static function (array $left, array $right): int {
            return strcmp($right['created_at'], $left['created_at']);
        });

        return array_map('market_normalize_order_summary', $orders);
    }

    try {
        if (market_has_normalized_orders()) {
            $paymentJoin = market_table_exists('order_payments') ? 'LEFT JOIN order_payments op ON op.order_id = o.id' : '';
            $paymentSelect = market_table_exists('order_payments')
                ? 'COALESCE(MAX(op.payment_method), "") AS payment_method,'
                : '"" AS payment_method,';

            $statement = $pdo->prepare(
                'SELECT o.order_number,
                        COALESCE(GROUP_CONCAT(p.title ORDER BY oi.id SEPARATOR ", "), "Product") AS item,
                        COALESCE(SUM(oi.quantity), 0) AS quantity,
                        o.status,
                        o.payment_status,
                        ' . $paymentSelect . '
                        o.delivery_method,
                        o.total_amount,
                        o.created_at
                 FROM orders o
                 LEFT JOIN order_items oi ON oi.order_id = o.id
                 LEFT JOIN products p ON p.id = oi.product_id
                 ' . $paymentJoin . '
                 WHERE o.user_id = :user_id
                 GROUP BY o.id, o.order_number, o.status, o.payment_status, o.delivery_method, o.total_amount, o.created_at
                 ORDER BY o.created_at DESC'
            );
            $statement->execute(['user_id' => $userId]);

            return array_map('market_normalize_order_summary', $statement->fetchAll());
        }

        $statement = $pdo->prepare(
            'SELECT o.order_number, p.title AS item, o.quantity, o.status, o.payment_method, o.delivery_method, o.total_amount, o.created_at
             FROM orders o
             INNER JOIN products p ON p.id = o.product_id
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map('market_normalize_order_summary', $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}

function market_get_buyer_stats(int $userId): array
{
    $orders = market_get_buyer_orders($userId);
    $openOrders = 0;
    $totalSpent = 0.0;

    foreach ($orders as $order) {
        if ($order['status'] === 'Pending' || $order['status'] === 'Paid') {
            $openOrders++;
        }

        $totalSpent += (float) str_replace(['R', ' '], '', $order['total']);
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
            $revenue += (float) $order['total_amount'];
        }

        return [
            ['label' => 'Users', 'value' => (string) count(market_sample_users())],
            ['label' => 'Products', 'value' => (string) count(market_sample_products())],
            ['label' => 'Orders', 'value' => (string) count($orders)],
            ['label' => 'Revenue', 'value' => market_format_money($revenue)],
        ];
    }

    try {
        $statement = $pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COUNT(*) FROM products) AS total_products,
                (SELECT COUNT(*) FROM orders) AS total_orders,
                (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> 'cancelled') AS total_revenue"
        );
        $row = $statement->fetch();

        return [
            ['label' => 'Users', 'value' => (string) ((int) ($row['total_users'] ?? 0))],
            ['label' => 'Products', 'value' => (string) ((int) ($row['total_products'] ?? 0))],
            ['label' => 'Orders', 'value' => (string) ((int) ($row['total_orders'] ?? 0))],
            ['label' => 'Revenue', 'value' => market_format_money((float) ($row['total_revenue'] ?? 0))],
        ];
    } catch (Throwable $exception) {
        return [
            ['label' => 'Users', 'value' => '0'],
            ['label' => 'Products', 'value' => '0'],
            ['label' => 'Orders', 'value' => '0'],
            ['label' => 'Revenue', 'value' => market_format_money(0)],
        ];
    }
}

function market_get_admin_recent_orders(int $limit = 5): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $orders = market_sample_orders();
        usort($orders, static function (array $left, array $right): int {
            return strcmp($right['created_at'], $left['created_at']);
        });

        return array_map('market_normalize_order_summary', array_slice($orders, 0, max(1, $limit)));
    }

    try {
        if (market_has_normalized_orders()) {
            $paymentJoin = market_table_exists('order_payments') ? 'LEFT JOIN order_payments op ON op.order_id = o.id' : '';
            $paymentSelect = market_table_exists('order_payments')
                ? 'COALESCE(MAX(op.payment_method), "") AS payment_method,'
                : '"" AS payment_method,';

            $statement = $pdo->query(
                'SELECT o.order_number,
                        u.full_name AS buyer,
                        COALESCE(GROUP_CONCAT(p.title ORDER BY oi.id SEPARATOR ", "), "Product") AS item,
                        COALESCE(SUM(oi.quantity), 0) AS quantity,
                        o.status,
                        o.payment_status,
                        ' . $paymentSelect . '
                        o.delivery_method,
                        o.total_amount,
                        o.created_at
                 FROM orders o
                 INNER JOIN users u ON u.id = o.user_id
                 LEFT JOIN order_items oi ON oi.order_id = o.id
                 LEFT JOIN products p ON p.id = oi.product_id
                 ' . $paymentJoin . '
                 GROUP BY o.id, o.order_number, u.full_name, o.status, o.payment_status, o.delivery_method, o.total_amount, o.created_at
                 ORDER BY o.created_at DESC
                 LIMIT ' . max(1, $limit)
            );

            return array_map('market_normalize_order_summary', $statement->fetchAll());
        }

        $statement = $pdo->query(
            'SELECT o.order_number, u.full_name AS buyer, p.title AS item, o.quantity, o.status, o.payment_method, o.delivery_method, o.total_amount, o.created_at
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             INNER JOIN products p ON p.id = o.product_id
             ORDER BY o.created_at DESC
             LIMIT ' . max(1, $limit)
        );

        return array_map('market_normalize_order_summary', $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}

function market_get_admin_recent_users(int $limit = 5): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $users = market_sample_users();
        usort($users, static function (array $left, array $right): int {
            return strcmp($right['created_at'], $left['created_at']);
        });

        return array_map(static function (array $user): array {
            $user = market_normalize_user($user);

            return [
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'] === 'admin' ? 'Admin' : 'Buyer',
                'status' => ucfirst($user['status']),
                'joined' => market_format_date($user['created_at']),
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
                'name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role'] === 'admin' ? 'Admin' : 'Buyer',
                'status' => ucfirst($row['status']),
                'joined' => market_format_date($row['created_at']),
            ];
        }, $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}

function market_get_seller_stats(): array
{
    $products = market_get_products();
    $orders = market_get_admin_recent_orders(100);
    $stockUnits = 0;
    $pendingOrders = 0;

    foreach ($products as $product) {
        $stockUnits += (int) $product['stock'];
    }

    foreach ($orders as $order) {
        if ($order['status'] === 'Pending' || $order['status'] === 'Paid') {
            $pendingOrders++;
        }
    }

    return [
        ['label' => 'Listings', 'value' => (string) count($products)],
        ['label' => 'Stock units', 'value' => (string) $stockUnits],
        ['label' => 'Received orders', 'value' => (string) count($orders)],
        ['label' => 'Open orders', 'value' => (string) $pendingOrders],
    ];
}

function market_get_seller_products(int $limit = 6): array
{
    return market_get_products(['limit' => max(1, $limit)]);
}

function market_get_seller_orders(int $limit = 6): array
{
    return market_get_admin_recent_orders(max(1, $limit));
}
