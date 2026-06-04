<?php

function market_database_unavailable_message(): string
{
    return 'Database is not connected yet. Import database/schema.sql and database/seed.sql, then update the LocalLink database settings.';
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
            'is_admin' => 0,
            'created_at' => '2026-05-29 08:00:00',
        ],
        [
            'id' => 2,
            'full_name' => 'Admin User',
            'email' => 'admin@locallink.market',
            'password_hash' => 'pbkdf2_sha256$200000$LcEaCRQ6IWNgkoBxxSA0Cg==$4NWQj6ETMe6mm6PZp8hWeUjx9re8YHPaqudT5N9XRD8=',
            'is_admin' => 1,
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
            'product_id' => 2,
            'quantity' => 1,
            'total_amount' => 380.00,
            'status' => 'paid',
            'delivery_method' => 'standard_delivery',
            'payment_method' => 'eft',
            'buyer_note' => 'Please message before delivery.',
            'created_at' => '2026-05-29 10:15:00',
        ],
        [
            'order_number' => 'LLM-1031',
            'user_id' => 1,
            'product_id' => 3,
            'quantity' => 1,
            'total_amount' => 220.00,
            'status' => 'completed',
            'delivery_method' => 'collection',
            'payment_method' => 'cash',
            'buyer_note' => 'Collecting after class.',
            'created_at' => '2026-05-28 09:00:00',
        ],
    ];
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
    $imagePath = trim((string) ($row['image_path'] ?? ''));

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

        $sql = 'SELECT p.id, p.category_id, p.title, p.description, p.price, p.stock, p.image_path, p.created_at, c.name AS category_name
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id
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
        $statement = $pdo->prepare(
            'SELECT p.id, p.category_id, p.title, p.description, p.price, p.stock, p.image_path, p.created_at, c.name AS category_name
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
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
                return $user;
            }
        }

        return null;
    }

    try {
        $statement = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, is_admin, created_at
             FROM users
             WHERE id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $user = $statement->fetch();

        return $user ?: null;
    } catch (Throwable $exception) {
        return null;
    }
}

function market_find_user_by_email(string $email): ?array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        foreach (market_sample_users() as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    $statement = $pdo->prepare(
        'SELECT id, full_name, email, password_hash, is_admin, created_at
         FROM users
         WHERE LOWER(email) = LOWER(:email)
         LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    return $user ?: null;
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

    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, is_admin)
         VALUES (:full_name, :email, :password_hash, 0)'
    );
    $statement->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => app_hash_password($password),
    ]);

    return (int) $pdo->lastInsertId();
}

function market_authenticate_user(string $email, string $password): ?array
{
    $user = market_find_user_by_email($email);

    if ($user === null) {
        return null;
    }

    return app_verify_password($password, $user['password_hash']) ? $user : null;
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

    $insert = $pdo->prepare(
        'INSERT INTO products (category_id, title, description, price, stock, image_path)
         VALUES (:category_id, :title, :description, :price, :stock, :image_path)'
    );
    $insert->execute([
        'category_id' => $categoryId,
        'title' => trim((string) $input['title']),
        'description' => trim((string) $input['description']),
        'price' => (float) $input['price'],
        'stock' => max(0, (int) ($input['stock'] ?? 0)),
        'image_path' => market_default_image_for_category((string) $categoryName),
    ]);

    return (int) $pdo->lastInsertId();
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

function market_create_order(
    int $userId,
    int $productId,
    int $quantity,
    string $deliveryMethod,
    string $paymentMethod,
    string $buyerNote = ''
): string {
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

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
        $totalAmount = (float) $product['price'] * $quantity;

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
            'status' => 'pending',
            'delivery_method' => $deliveryMethod,
            'payment_method' => $paymentMethod,
            'buyer_note' => $buyerNote,
        ]);

        $updateStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :product_id');
        $updateStock->execute([
            'quantity' => $quantity,
            'product_id' => $productId,
        ]);

        $pdo->commit();

        return $orderNumber;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
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

function market_get_buyer_orders(int $userId): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        $products = [];
        foreach (market_sample_products() as $product) {
            $products[(int) $product['id']] = $product;
        }

        $orders = array_values(array_filter(market_sample_orders(), static function (array $order) use ($userId): bool {
            return (int) $order['user_id'] === $userId;
        }));

        usort($orders, static function (array $left, array $right): int {
            return strcmp($right['created_at'], $left['created_at']);
        });

        return array_map(static function (array $order) use ($products): array {
            $product = $products[(int) $order['product_id']] ?? ['title' => 'Product'];

            return [
                'code' => '#' . $order['order_number'],
                'item' => $product['title'],
                'quantity' => (int) $order['quantity'],
                'status' => market_humanize_order_status($order['status']),
                'total' => market_format_money((float) $order['total_amount']),
                'placed_on' => market_format_date($order['created_at']),
            ];
        }, $orders);
    }

    try {
        $statement = $pdo->prepare(
            'SELECT o.order_number, o.quantity, o.total_amount, o.status, o.created_at, p.title
             FROM orders o
             INNER JOIN products p ON p.id = o.product_id
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(static function (array $row): array {
            return [
                'code' => '#' . $row['order_number'],
                'item' => $row['title'],
                'quantity' => (int) $row['quantity'],
                'status' => market_humanize_order_status($row['status']),
                'total' => market_format_money((float) $row['total_amount']),
                'placed_on' => market_format_date($row['created_at']),
            ];
        }, $statement->fetchAll());
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
        $users = [];
        foreach (market_sample_users() as $user) {
            $users[(int) $user['id']] = $user;
        }

        $products = [];
        foreach (market_sample_products() as $product) {
            $products[(int) $product['id']] = $product;
        }

        $orders = market_sample_orders();
        usort($orders, static function (array $left, array $right): int {
            return strcmp($right['created_at'], $left['created_at']);
        });

        $orders = array_slice($orders, 0, max(1, $limit));

        return array_map(static function (array $order) use ($users, $products): array {
            return [
                'code' => '#' . $order['order_number'],
                'customer' => $users[(int) $order['user_id']]['full_name'] ?? 'Customer',
                'item' => $products[(int) $order['product_id']]['title'] ?? 'Product',
                'quantity' => (int) $order['quantity'],
                'status' => market_humanize_order_status($order['status']),
                'total' => market_format_money((float) $order['total_amount']),
                'placed_on' => market_format_date($order['created_at']),
            ];
        }, $orders);
    }

    try {
        $statement = $pdo->query(
            'SELECT o.order_number, o.quantity, o.total_amount, o.status, o.created_at, u.full_name, p.title
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             INNER JOIN products p ON p.id = o.product_id
             ORDER BY o.created_at DESC
             LIMIT ' . max(1, $limit)
        );

        return array_map(static function (array $row): array {
            return [
                'code' => '#' . $row['order_number'],
                'customer' => $row['full_name'],
                'item' => $row['title'],
                'quantity' => (int) $row['quantity'],
                'status' => market_humanize_order_status($row['status']),
                'total' => market_format_money((float) $row['total_amount']),
                'placed_on' => market_format_date($row['created_at']),
            ];
        }, $statement->fetchAll());
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
            return [
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => !empty($user['is_admin']) ? 'Admin' : 'Customer',
                'joined' => market_format_date($user['created_at']),
            ];
        }, array_slice($users, 0, max(1, $limit)));
    }

    try {
        $statement = $pdo->query(
            'SELECT full_name, email, is_admin, created_at
             FROM users
             ORDER BY created_at DESC
             LIMIT ' . max(1, $limit)
        );

        return array_map(static function (array $row): array {
            return [
                'name' => $row['full_name'],
                'email' => $row['email'],
                'role' => !empty($row['is_admin']) ? 'Admin' : 'Customer',
                'joined' => market_format_date($row['created_at']),
            ];
        }, $statement->fetchAll());
    } catch (Throwable $exception) {
        return [];
    }
}
