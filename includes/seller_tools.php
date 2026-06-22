<?php
require_once __DIR__ . '/app.php';

function seller_navigation_items(): array
{
    return [
        ['key' => 'overview', 'label' => 'Overview', 'path' => 'seller-dashboard.php'],
        ['key' => 'products', 'label' => 'Products', 'path' => 'seller-products.php'],
        ['key' => 'orders', 'label' => 'Orders', 'path' => 'seller-orders.php'],
        ['key' => 'profile', 'label' => 'Profile', 'path' => 'seller-profile.php'],
    ];
}

function seller_humanize_label(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '-';
    }

    return ucwords(str_replace('_', ' ', $value));
}

function seller_humanize_product_status(string $status): string
{
    $labels = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'hidden' => 'Hidden',
        'sold' => 'Sold',
        'archived' => 'Archived',
    ];

    return $labels[$status] ?? seller_humanize_label($status);
}

function seller_humanize_order_status(string $status): string
{
    $labels = [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'paid' => 'Paid',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    return $labels[$status] ?? seller_humanize_label($status);
}

function seller_humanize_verification_status(string $status): string
{
    $labels = [
        'not_requested' => 'Not requested',
        'pending' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Needs changes',
    ];

    return $labels[$status] ?? seller_humanize_label($status);
}

function seller_capabilities(): array
{
    static $capabilities = null;

    if ($capabilities !== null) {
        return $capabilities;
    }

    $hasDatabase = db_is_available();
    $hasSellerProfiles = $hasDatabase && market_table_exists('seller_profiles');
    $hasProductOwner = $hasDatabase && market_table_has_column('products', 'seller_id');
    $hasProductStatus = $hasDatabase && market_table_has_column('products', 'status');
    $hasProductQuantity = $hasDatabase && market_table_has_column('products', 'quantity');
    $hasProductStock = $hasDatabase && market_table_has_column('products', 'stock');
    $hasProductLocation = $hasDatabase && market_table_has_column('products', 'location');
    $hasOrderItems = $hasDatabase && market_table_exists('order_items');
    $hasOrderSeller = $hasDatabase && market_orders_have_column('seller_id');
    $hasLegacyOrderProduct = $hasDatabase && market_orders_have_column('product_id');
    $hasOrderSupport = $hasDatabase && ($hasOrderSeller || ($hasProductOwner && ($hasOrderItems || $hasLegacyOrderProduct)));
    $orderStatusColumn = null;

    if ($hasDatabase) {
        if (market_orders_have_column('order_status')) {
            $orderStatusColumn = 'order_status';
        } elseif (market_orders_have_column('status')) {
            $orderStatusColumn = 'status';
        }
    }

    $productStatuses = [];
    if ($hasProductStatus) {
        if ($hasProductQuantity || ($hasProductOwner && $hasProductLocation)) {
            $productStatuses = ['active', 'inactive', 'hidden', 'sold'];
        } else {
            $productStatuses = ['active', 'archived'];
        }
    }

    $orderStatuses = [];
    if ($orderStatusColumn === 'order_status') {
        $orderStatuses = ['pending', 'accepted', 'paid', 'ready', 'completed', 'cancelled'];
    } elseif ($orderStatusColumn === 'status') {
        $orderStatuses = ['pending', 'paid', 'completed', 'cancelled'];
    }

    $capabilities = [
        'database' => $hasDatabase,
        'profile_support' => $hasSellerProfiles,
        'product_owner_support' => $hasProductOwner,
        'product_status_support' => $hasProductStatus,
        'product_location_support' => $hasProductLocation,
        'product_quantity_column' => $hasProductQuantity ? 'quantity' : ($hasProductStock ? 'stock' : null),
        'order_support' => $hasOrderSupport,
        'order_status_column' => $orderStatusColumn,
        'product_statuses' => $productStatuses,
        'order_statuses' => $orderStatuses,
    ];

    return $capabilities;
}

function seller_product_status_options(): array
{
    $options = seller_capabilities()['product_statuses'];

    return $options !== [] ? $options : ['active'];
}

function seller_order_status_options(): array
{
    $options = seller_capabilities()['order_statuses'];

    return $options !== [] ? $options : ['pending'];
}

function seller_fetch_raw_user(int $userId): array
{
    $pdo = db_try_get_connection();

    if (!$pdo) {
        return [];
    }

    $columns = ['id', 'full_name', 'email', 'created_at'];

    foreach (['role', 'status', 'phone', 'location'] as $column) {
        if (market_users_have_column($column)) {
            $columns[] = $column;
        }
    }

    try {
        $statement = $pdo->prepare(
            'SELECT ' . implode(', ', array_unique($columns)) . '
             FROM users
             WHERE id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : [];
    } catch (Throwable $exception) {
        return [];
    }
}

function seller_fetch_profile(int $userId): array
{
    $pdo = db_try_get_connection();

    if (!$pdo || !market_table_exists('seller_profiles')) {
        return [];
    }

    $columns = ['user_id', 'business_name', 'location', 'verification_status', 'verification_notes', 'created_at'];
    $available = [];

    foreach ($columns as $column) {
        if (market_table_has_column('seller_profiles', $column)) {
            $available[] = $column;
        }
    }

    if ($available === []) {
        return [];
    }

    try {
        $statement = $pdo->prepare(
            'SELECT ' . implode(', ', $available) . '
             FROM seller_profiles
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : [];
    } catch (Throwable $exception) {
        return [];
    }
}

function seller_build_user_context(array $user): array
{
    $rawUser = seller_fetch_raw_user((int) $user['id']);
    $profile = seller_fetch_profile((int) $user['id']);
    $context = array_merge($user, $rawUser);
    $rawRole = strtolower((string) ($rawUser['role'] ?? $context['role'] ?? ''));

    if ($rawRole === '') {
        $rawRole = !empty($context['is_admin']) ? 'admin' : 'buyer';
    }

    $businessName = trim((string) ($profile['business_name'] ?? ''));
    $location = trim((string) ($profile['location'] ?? $rawUser['location'] ?? ''));
    $verificationStatus = strtolower((string) ($profile['verification_status'] ?? 'not_requested'));

    $context['raw_role'] = $rawRole;
    $context['business_name'] = $businessName;
    $context['location'] = $location;
    $context['verification_status'] = $verificationStatus;
    $context['verification_label'] = seller_humanize_verification_status($verificationStatus);
    $context['verification_notes'] = trim((string) ($profile['verification_notes'] ?? ''));
    $context['profile_exists'] = $profile !== [];
    $context['seller_display_name'] = $businessName !== '' ? $businessName : (string) ($context['full_name'] ?? 'Seller');
    $context['joined_on'] = market_format_date((string) ($context['created_at'] ?? ''));
    $context['can_access_seller'] = in_array($rawRole, ['seller', 'admin', 'super_admin'], true)
        || $context['profile_exists']
        || $verificationStatus !== 'not_requested';

    return $context;
}

function seller_dashboard_path_for_context(array $sellerUser): string
{
    if (in_array((string) ($sellerUser['raw_role'] ?? ''), ['admin', 'super_admin'], true)) {
        return 'admin/dashboard.php';
    }

    if (!empty($sellerUser['can_access_seller'])) {
        return 'seller-dashboard.php';
    }

    return 'buyer-dashboard.php';
}

function seller_require_account(): array
{
    $user = app_require_login();
    $sellerUser = seller_build_user_context($user);
    $rawRole = (string) ($sellerUser['raw_role'] ?? 'buyer');
    $verificationStatus = (string) ($sellerUser['verification_status'] ?? 'not_requested');

    if (in_array($rawRole, ['seller', 'admin', 'super_admin'], true)) {
        return $sellerUser;
    }

    if ($verificationStatus === 'pending') {
        app_set_flash('error', 'Your seller request is still under review. You can update your profile while you wait.');
    } elseif ($verificationStatus === 'rejected') {
        app_set_flash('error', 'Update your seller profile and submit your verification request again.');
    } else {
        app_set_flash('error', 'Complete your seller profile and request verification before opening seller tools.');
    }

    app_redirect('seller-profile.php');
}

function seller_filter_products(array $products, array $filters = []): array
{
    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $status = strtolower(trim((string) ($filters['status'] ?? '')));
    $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 0;

    $products = array_values(array_filter($products, static function (array $product) use ($search, $status): bool {
        if ($status !== '' && strtolower((string) ($product['raw_status'] ?? '')) !== $status) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = strtolower(
            implode(' ', [
                (string) ($product['title'] ?? ''),
                (string) ($product['description'] ?? ''),
                (string) ($product['category'] ?? ''),
                (string) ($product['location'] ?? ''),
            ])
        );

        return strpos($haystack, $search) !== false;
    }));

    if ($limit > 0) {
        $products = array_slice($products, 0, $limit);
    }

    return $products;
}

function seller_query_products(array $sellerUser): array
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if (!$pdo || !$capabilities['product_owner_support']) {
        return [];
    }

    $stockColumn = $capabilities['product_quantity_column'] ?? 'stock';
    $statusSelect = $capabilities['product_status_support'] ? 'p.status' : "'active'";
    $locationSelect = $capabilities['product_location_support'] ? 'p.location' : "''";
    $updatedSelect = market_table_has_column('products', 'updated_at') ? 'p.updated_at' : 'p.created_at';
    $imageJoin = market_has_product_images() ? 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1' : '';
    $imageSelect = market_has_product_images()
        ? 'COALESCE(pi.image_path, p.image_path) AS resolved_image_path'
        : 'p.image_path AS resolved_image_path';

    try {
        $statement = $pdo->prepare(
            'SELECT p.id,
                    p.category_id,
                    p.title,
                    p.description,
                    p.price,
                    p.' . $stockColumn . ' AS stock,
                    p.image_path,
                    p.created_at,
                    ' . $updatedSelect . ' AS updated_at,
                    ' . $statusSelect . ' AS raw_status,
                    ' . $locationSelect . ' AS location,
                    ' . $imageSelect . ',
                    c.name AS category_name
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             ' . $imageJoin . '
             WHERE p.seller_id = :seller_id
             ORDER BY ' . $updatedSelect . ' DESC, p.id DESC'
        );
        $statement->execute(['seller_id' => (int) $sellerUser['id']]);
        $rows = $statement->fetchAll();
    } catch (Throwable $exception) {
        return [];
    }

    return array_map(static function (array $row): array {
        $product = market_map_product($row);
        $product['raw_status'] = strtolower((string) ($row['raw_status'] ?? 'active'));
        $product['status'] = seller_humanize_product_status($product['raw_status']);
        $product['location'] = trim((string) ($row['location'] ?? ''));
        $product['updated_on'] = market_format_date((string) ($row['updated_at'] ?? $row['created_at'] ?? ''));

        return $product;
    }, $rows);
}

function seller_sample_products(array $sellerUser): array
{
    $products = market_get_products(['limit' => 12]);

    return array_map(static function (array $product, int $index) use ($sellerUser): array {
        $statuses = ['active', 'active', 'hidden', 'sold'];
        $product['raw_status'] = $statuses[$index % count($statuses)];
        $product['status'] = seller_humanize_product_status($product['raw_status']);
        $product['location'] = $index % 2 === 0 ? 'Johannesburg pickup' : 'Pretoria delivery zone';
        $product['updated_on'] = $product['created_at'] !== '' ? market_format_date($product['created_at']) : date('j M Y');
        $product['seller_name'] = $sellerUser['seller_display_name'];

        return $product;
    }, $products, array_keys($products));
}

function seller_get_products_for_user(array $sellerUser, array $filters = []): array
{
    $products = seller_query_products($sellerUser);

    if ($products === []) {
        $products = seller_sample_products($sellerUser);
    }

    return seller_filter_products($products, $filters);
}

function seller_find_product_for_user(array $sellerUser, int $productId): ?array
{
    foreach (seller_get_products_for_user($sellerUser) as $product) {
        if ((int) $product['id'] === $productId) {
            return $product;
        }
    }

    return null;
}

function seller_sync_product_image(PDO $pdo, int $productId, string $imagePath): void
{
    if (!market_has_product_images()) {
        return;
    }

    $hasPrimary = market_table_has_column('product_images', 'is_primary');
    $hasSortOrder = market_table_has_column('product_images', 'sort_order');

    try {
        $where = $hasPrimary ? 'product_id = :product_id AND is_primary = 1' : 'product_id = :product_id';
        $statement = $pdo->prepare(
            'SELECT id
             FROM product_images
             WHERE ' . $where . '
             ORDER BY id ASC
             LIMIT 1'
        );
        $statement->execute(['product_id' => $productId]);
        $existingId = $statement->fetchColumn();

        if ($existingId !== false) {
            $update = $pdo->prepare(
                'UPDATE product_images
                 SET image_path = :image_path
                 WHERE id = :image_id'
            );
            $update->execute([
                'image_path' => $imagePath,
                'image_id' => (int) $existingId,
            ]);

            return;
        }

        $columns = ['product_id', 'image_path'];
        $values = [':product_id', ':image_path'];
        $params = [
            'product_id' => $productId,
            'image_path' => $imagePath,
        ];

        if ($hasPrimary) {
            $columns[] = 'is_primary';
            $values[] = '1';
        }

        if ($hasSortOrder) {
            $columns[] = 'sort_order';
            $values[] = '1';
        }

        $insert = $pdo->prepare(
            'INSERT INTO product_images (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', $values) . ')'
        );
        $insert->execute($params);
    } catch (Throwable $exception) {
        return;
    }
}

function seller_save_product(array $sellerUser, array $input, ?int $productId = null): int
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    if (!$capabilities['product_owner_support']) {
        throw new RuntimeException('Seller product ownership support is still pending in the shared schema.');
    }

    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $categoryId = max(0, (int) ($input['category_id'] ?? 0));
    $price = (float) ($input['price'] ?? 0);
    $stock = max(0, (int) ($input['stock'] ?? 0));
    $location = trim((string) ($input['location'] ?? ''));
    $allowedStatuses = seller_product_status_options();
    $status = strtolower(trim((string) ($input['status'] ?? $allowedStatuses[0])));

    if ($title === '') {
        throw new RuntimeException('Enter a product title.');
    }

    if ($description === '') {
        throw new RuntimeException('Add a short product description.');
    }

    if ($categoryId < 1) {
        throw new RuntimeException('Choose a category.');
    }

    if ($price <= 0) {
        throw new RuntimeException('Enter a valid price.');
    }

    if ($stock < 1) {
        throw new RuntimeException('Stock must be at least 1.');
    }

    if (!in_array($status, $allowedStatuses, true)) {
        $status = $allowedStatuses[0];
    }

    $categoryLookup = $pdo->prepare('SELECT name FROM categories WHERE id = :category_id LIMIT 1');
    $categoryLookup->execute(['category_id' => $categoryId]);
    $categoryName = $categoryLookup->fetchColumn();

    if ($categoryName === false) {
        throw new RuntimeException('Choose a valid category.');
    }

    $imagePath = market_default_image_for_category((string) $categoryName);
    $stockColumn = $capabilities['product_quantity_column'] ?? 'stock';

    if ($productId !== null) {
        $ownerCheck = $pdo->prepare(
            'SELECT id
             FROM products
             WHERE id = :product_id AND seller_id = :seller_id
             LIMIT 1'
        );
        $ownerCheck->execute([
            'product_id' => $productId,
            'seller_id' => (int) $sellerUser['id'],
        ]);

        if ($ownerCheck->fetchColumn() === false) {
            throw new RuntimeException('That product could not be loaded for this seller account.');
        }
    }

    $params = [
        'category_id' => $categoryId,
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'stock_value' => $stock,
        'seller_id' => (int) $sellerUser['id'],
        'image_path' => $imagePath,
    ];

    if ($productId === null) {
        $columns = ['seller_id', 'category_id', 'title', 'description', 'price', $stockColumn];
        $values = [':seller_id', ':category_id', ':title', ':description', ':price', ':stock_value'];

        if ($capabilities['product_location_support']) {
            $columns[] = 'location';
            $values[] = ':location';
            $params['location'] = $location;
        }

        if ($capabilities['product_status_support']) {
            $columns[] = 'status';
            $values[] = ':status';
            $params['status'] = $status;
        }

        if (market_table_has_column('products', 'image_path')) {
            $columns[] = 'image_path';
            $values[] = ':image_path';
        }

        $insert = $pdo->prepare(
            'INSERT INTO products (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', $values) . ')'
        );
        $insert->execute($params);
        $savedProductId = (int) $pdo->lastInsertId();
        seller_sync_product_image($pdo, $savedProductId, $imagePath);

        return $savedProductId;
    }
    $assignments = [
        'category_id = :category_id',
        'title = :title',
        'description = :description',
        'price = :price',
        $stockColumn . ' = :stock_value',
    ];
    $params['product_id'] = $productId;

    if ($capabilities['product_location_support']) {
        $assignments[] = 'location = :location';
        $params['location'] = $location;
    }

    if ($capabilities['product_status_support']) {
        $assignments[] = 'status = :status';
        $params['status'] = $status;
    }

    if (market_table_has_column('products', 'image_path')) {
        $assignments[] = 'image_path = :image_path';
    }

    $update = $pdo->prepare(
        'UPDATE products
         SET ' . implode(', ', $assignments) . '
         WHERE id = :product_id AND seller_id = :seller_id'
    );
    $update->execute($params);
    seller_sync_product_image($pdo, $productId, $imagePath);

    return $productId;
}

function seller_query_orders(array $sellerUser): array
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if (!$pdo || !$capabilities['order_support']) {
        return [];
    }

    $statusColumn = $capabilities['order_status_column'] ?? 'status';
    $statusExpr = 'o.' . $statusColumn;
    $buyerIdColumn = market_orders_have_column('buyer_id') ? 'buyer_id' : (market_orders_have_column('user_id') ? 'user_id' : null);
    $buyerJoin = $buyerIdColumn !== null ? 'LEFT JOIN users u ON u.id = o.' . $buyerIdColumn : '';
    $paymentJoin = market_table_exists('order_payments') ? 'LEFT JOIN order_payments op ON op.order_id = o.id' : '';
    $addressJoin = market_table_exists('order_addresses') ? 'LEFT JOIN order_addresses oa ON oa.order_id = o.id' : '';
    $deliveryExpr = market_orders_have_column('delivery_method') ? 'o.delivery_method' : "'collection'";
    $totalExpr = market_orders_have_column('total_amount') ? 'o.total_amount' : '0';
    $paymentStatusExpr = market_orders_have_column('payment_status')
        ? 'o.payment_status'
        : (market_table_exists('order_payments') ? "COALESCE(MAX(op.payment_status), 'pending')" : "'pending'");
    $paymentMethodExpr = market_orders_have_column('payment_method')
        ? 'o.payment_method'
        : (market_table_exists('order_payments') ? "COALESCE(MAX(op.payment_method), 'eft')" : "'eft'");
    $buyerNoteExpr = market_orders_have_column('buyer_note') ? 'o.buyer_note' : "''";
    $updatedExpr = market_orders_have_column('updated_at') ? 'o.updated_at' : 'o.created_at';

    try {
        if (market_table_exists('order_items')) {
            if ($capabilities['product_owner_support']) {
                $sellerWhere = market_orders_have_column('seller_id')
                    ? 'o.seller_id = :seller_id'
                    : 'EXISTS (
                        SELECT 1
                        FROM order_items soi
                        INNER JOIN products sp ON sp.id = soi.product_id
                        WHERE soi.order_id = o.id AND sp.seller_id = :seller_id
                    )';
            } elseif (market_orders_have_column('seller_id')) {
                $sellerWhere = 'o.seller_id = :seller_id';
            } else {
                return [];
            }

            $statement = $pdo->prepare(
                'SELECT o.id,
                        o.order_number,
                        COALESCE(MAX(u.full_name), "Buyer") AS buyer_name,
                        COALESCE(MAX(u.email), "") AS buyer_email,
                        COALESCE(GROUP_CONCAT(DISTINCT p.title ORDER BY p.title SEPARATOR ", "), "Product") AS item_summary,
                        COALESCE(SUM(oi.quantity), 0) AS quantity_total,
                        ' . $statusExpr . ' AS raw_status,
                        ' . $paymentStatusExpr . ' AS raw_payment_status,
                        ' . $paymentMethodExpr . ' AS raw_payment_method,
                        ' . $deliveryExpr . ' AS raw_delivery_method,
                        ' . $totalExpr . ' AS total_amount,
                        ' . $buyerNoteExpr . ' AS buyer_note,
                        o.created_at,
                        ' . $updatedExpr . ' AS updated_at,
                        COALESCE(MAX(oa.contact_name), "") AS contact_name,
                        COALESCE(MAX(oa.phone_number), "") AS phone_number,
                        COALESCE(MAX(oa.address_line_1), "") AS address_line_1,
                        COALESCE(MAX(oa.address_line_2), "") AS address_line_2,
                        COALESCE(MAX(oa.city), "") AS city,
                        COALESCE(MAX(oa.postal_code), "") AS postal_code,
                        COALESCE(MAX(oa.collection_note), "") AS collection_note
                 FROM orders o
                 ' . $buyerJoin . '
                 LEFT JOIN order_items oi ON oi.order_id = o.id
                 LEFT JOIN products p ON p.id = oi.product_id
                 ' . $paymentJoin . '
                 ' . $addressJoin . '
                 WHERE ' . $sellerWhere . '
                 GROUP BY o.id, o.order_number, ' . $statusExpr . ', ' . $deliveryExpr . ', ' . $totalExpr . ', ' . $buyerNoteExpr . ', o.created_at, ' . $updatedExpr . '
                 ORDER BY ' . $updatedExpr . ' DESC, o.id DESC'
            );
            $statement->execute(['seller_id' => (int) $sellerUser['id']]);
        } elseif ($capabilities['product_owner_support'] && market_orders_have_column('product_id')) {
            $statement = $pdo->prepare(
                'SELECT o.id,
                        o.order_number,
                        COALESCE(u.full_name, "Buyer") AS buyer_name,
                        COALESCE(u.email, "") AS buyer_email,
                        p.title AS item_summary,
                        COALESCE(o.quantity, 0) AS quantity_total,
                        ' . $statusExpr . ' AS raw_status,
                        ' . $paymentStatusExpr . ' AS raw_payment_status,
                        ' . $paymentMethodExpr . ' AS raw_payment_method,
                        ' . $deliveryExpr . ' AS raw_delivery_method,
                        ' . $totalExpr . ' AS total_amount,
                        ' . $buyerNoteExpr . ' AS buyer_note,
                        o.created_at,
                        ' . $updatedExpr . ' AS updated_at,
                        "" AS contact_name,
                        "" AS phone_number,
                        "" AS address_line_1,
                        "" AS address_line_2,
                        "" AS city,
                        "" AS postal_code,
                        "" AS collection_note
                 FROM orders o
                 LEFT JOIN users u ON u.id = o.user_id
                 INNER JOIN products p ON p.id = o.product_id
                 WHERE p.seller_id = :seller_id
                 ORDER BY ' . $updatedExpr . ' DESC, o.id DESC'
            );
            $statement->execute(['seller_id' => (int) $sellerUser['id']]);
        } else {
            return [];
        }

        $rows = $statement->fetchAll();
    } catch (Throwable $exception) {
        return [];
    }

    return array_map(static function (array $row): array {
        $rawStatus = strtolower((string) ($row['raw_status'] ?? 'pending'));
        $rawPaymentStatus = strtolower((string) ($row['raw_payment_status'] ?? 'pending'));
        $rawPaymentMethod = strtolower((string) ($row['raw_payment_method'] ?? 'eft'));
        $totalAmount = (float) ($row['total_amount'] ?? 0);

        return [
            'id' => (int) $row['id'],
            'code' => '#' . (string) ($row['order_number'] ?? ''),
            'buyer_name' => (string) ($row['buyer_name'] ?? 'Buyer'),
            'buyer_email' => (string) ($row['buyer_email'] ?? ''),
            'item_summary' => (string) ($row['item_summary'] ?? 'Product'),
            'quantity_total' => (int) ($row['quantity_total'] ?? 0),
            'raw_status' => $rawStatus,
            'status' => seller_humanize_order_status($rawStatus),
            'raw_payment_status' => $rawPaymentStatus,
            'payment_status' => market_humanize_payment_status($rawPaymentStatus),
            'raw_payment_method' => $rawPaymentMethod,
            'payment_method' => market_humanize_payment_method($rawPaymentMethod),
            'raw_delivery_method' => (string) ($row['raw_delivery_method'] ?? 'collection'),
            'delivery_method' => market_humanize_delivery_method((string) ($row['raw_delivery_method'] ?? 'collection')),
            'total_amount_value' => $totalAmount,
            'total' => market_format_money($totalAmount),
            'buyer_note' => trim((string) ($row['buyer_note'] ?? '')),
            'placed_on' => market_format_date((string) ($row['created_at'] ?? '')),
            'updated_on' => market_format_date((string) ($row['updated_at'] ?? $row['created_at'] ?? '')),
            'contact_name' => trim((string) ($row['contact_name'] ?? '')),
            'phone_number' => trim((string) ($row['phone_number'] ?? '')),
            'address_line_1' => trim((string) ($row['address_line_1'] ?? '')),
            'address_line_2' => trim((string) ($row['address_line_2'] ?? '')),
            'city' => trim((string) ($row['city'] ?? '')),
            'postal_code' => trim((string) ($row['postal_code'] ?? '')),
            'collection_note' => trim((string) ($row['collection_note'] ?? '')),
        ];
    }, $rows);
}

function seller_sample_orders(array $sellerUser): array
{
    $orders = market_get_admin_recent_orders(12);

    return array_map(static function (array $order, int $index) use ($sellerUser): array {
        $statusCycle = ['pending', 'accepted', 'ready', 'completed'];
        $status = $statusCycle[$index % count($statusCycle)];
        $paymentRaw = $index % 2 === 0 ? 'paid' : 'awaiting_confirmation';

        return [
            'id' => $index + 1,
            'code' => (string) ($order['code'] ?? '#LLM-1000'),
            'buyer_name' => (string) ($order['buyer'] ?? 'Buyer'),
            'buyer_email' => 'buyer' . ($index + 1) . '@locallink.market',
            'item_summary' => (string) ($order['item'] ?? 'Product'),
            'quantity_total' => (int) ($order['quantity'] ?? 1),
            'raw_status' => $status,
            'status' => seller_humanize_order_status($status),
            'raw_payment_status' => $paymentRaw,
            'payment_status' => market_humanize_payment_status($paymentRaw),
            'raw_payment_method' => $index % 2 === 0 ? 'card' : 'eft',
            'payment_method' => $index % 2 === 0 ? 'Card' : 'EFT',
            'raw_delivery_method' => 'collection',
            'delivery_method' => 'Collection',
            'total_amount_value' => 250 + ($index * 35),
            'total' => market_format_money(250 + ($index * 35)),
            'buyer_note' => 'Sample seller order preview for ' . $sellerUser['seller_display_name'] . '.',
            'placed_on' => (string) ($order['placed_on'] ?? market_format_date(date('Y-m-d'))),
            'updated_on' => (string) ($order['placed_on'] ?? market_format_date(date('Y-m-d'))),
            'contact_name' => (string) ($order['buyer'] ?? 'Buyer'),
            'phone_number' => '082 000 000' . (($index % 9) + 1),
            'address_line_1' => '',
            'address_line_2' => '',
            'city' => '',
            'postal_code' => '',
            'collection_note' => 'Buyer will collect from the agreed pickup point.',
        ];
    }, $orders, array_keys($orders));
}

function seller_filter_orders(array $orders, array $filters = []): array
{
    $search = strtolower(trim((string) ($filters['search'] ?? '')));
    $status = strtolower(trim((string) ($filters['status'] ?? '')));
    $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 0;

    $orders = array_values(array_filter($orders, static function (array $order) use ($search, $status): bool {
        if ($status !== '' && strtolower((string) ($order['raw_status'] ?? '')) !== $status) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = strtolower(
            implode(' ', [
                (string) ($order['code'] ?? ''),
                (string) ($order['buyer_name'] ?? ''),
                (string) ($order['buyer_email'] ?? ''),
                (string) ($order['item_summary'] ?? ''),
            ])
        );

        return strpos($haystack, $search) !== false;
    }));

    if ($limit > 0) {
        $orders = array_slice($orders, 0, $limit);
    }

    return $orders;
}

function seller_get_orders_for_user(array $sellerUser, array $filters = []): array
{
    $orders = seller_query_orders($sellerUser);

    if ($orders === []) {
        $orders = seller_sample_orders($sellerUser);
    }

    return seller_filter_orders($orders, $filters);
}

function seller_get_order_items(array $sellerUser, int $orderId): array
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if ($pdo && $capabilities['order_support']) {
        try {
            if (market_table_exists('order_items')) {
                $where = 'oi.order_id = :order_id';

                if ($capabilities['product_owner_support']) {
                    $where .= ' AND p.seller_id = :seller_id';
                }

                $statement = $pdo->prepare(
                    'SELECT p.id AS product_id,
                            COALESCE(p.title, "Product") AS title,
                            COALESCE(oi.quantity, 0) AS quantity,
                            COALESCE(oi.unit_price, 0) AS unit_price,
                            COALESCE(oi.line_total, 0) AS line_total
                     FROM order_items oi
                     LEFT JOIN products p ON p.id = oi.product_id
                     WHERE ' . $where . '
                     ORDER BY oi.id ASC'
                );
                $params = ['order_id' => $orderId];

                if ($capabilities['product_owner_support']) {
                    $params['seller_id'] = (int) $sellerUser['id'];
                }

                $statement->execute($params);
                $rows = $statement->fetchAll();

                if ($rows !== []) {
                    return array_map(static function (array $row): array {
                        return [
                            'product_id' => (int) ($row['product_id'] ?? 0),
                            'title' => (string) ($row['title'] ?? 'Product'),
                            'quantity' => (int) ($row['quantity'] ?? 0),
                            'unit_price' => market_format_money((float) ($row['unit_price'] ?? 0)),
                            'line_total' => market_format_money((float) ($row['line_total'] ?? 0)),
                        ];
                    }, $rows);
                }
            } elseif (market_orders_have_column('product_id')) {
                $statement = $pdo->prepare(
                    'SELECT p.id AS product_id,
                            p.title,
                            o.quantity,
                            p.price AS unit_price,
                            o.total_amount AS line_total
                     FROM orders o
                     INNER JOIN products p ON p.id = o.product_id
                     WHERE o.id = :order_id AND p.seller_id = :seller_id
                     LIMIT 1'
                );
                $statement->execute([
                    'order_id' => $orderId,
                    'seller_id' => (int) $sellerUser['id'],
                ]);
                $row = $statement->fetch();

                if ($row) {
                    return [[
                        'product_id' => (int) ($row['product_id'] ?? 0),
                        'title' => (string) ($row['title'] ?? 'Product'),
                        'quantity' => (int) ($row['quantity'] ?? 0),
                        'unit_price' => market_format_money((float) ($row['unit_price'] ?? 0)),
                        'line_total' => market_format_money((float) ($row['line_total'] ?? 0)),
                    ]];
                }
            }
        } catch (Throwable $exception) {
            return [];
        }
    }

    foreach (seller_sample_orders($sellerUser) as $order) {
        if ((int) $order['id'] === $orderId) {
            return [[
                'product_id' => 0,
                'title' => $order['item_summary'],
                'quantity' => $order['quantity_total'],
                'unit_price' => $order['total'],
                'line_total' => $order['total'],
            ]];
        }
    }

    return [];
}

function seller_get_order_for_user(array $sellerUser, int $orderId): ?array
{
    foreach (seller_get_orders_for_user($sellerUser) as $order) {
        if ((int) $order['id'] === $orderId) {
            $order['items'] = seller_get_order_items($sellerUser, $orderId);

            return $order;
        }
    }

    return null;
}

function seller_update_order_status(array $sellerUser, int $orderId, string $status): void
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    if (!$capabilities['order_support'] || $capabilities['order_status_column'] === null) {
        throw new RuntimeException('Seller order status updates are still waiting for shared schema support.');
    }

    $allowedStatuses = seller_order_status_options();
    $status = strtolower(trim($status));

    if (!in_array($status, $allowedStatuses, true)) {
        throw new RuntimeException('Choose a valid order status.');
    }

    $order = seller_get_order_for_user($sellerUser, $orderId);

    if ($order === null) {
        throw new RuntimeException('That order could not be loaded for this seller account.');
    }

    $statement = $pdo->prepare(
        'UPDATE orders
         SET ' . $capabilities['order_status_column'] . ' = :status
         WHERE id = :order_id'
    );
    $statement->execute([
        'status' => $status,
        'order_id' => $orderId,
    ]);
}

function seller_get_dashboard_stats(array $sellerUser): array
{
    $products = seller_get_products_for_user($sellerUser);
    $orders = seller_get_orders_for_user($sellerUser);
    $stockUnits = 0;
    $activeListings = 0;
    $openOrders = 0;
    $revenue = 0.0;

    foreach ($products as $product) {
        $stockUnits += (int) ($product['stock'] ?? 0);

        if ((string) ($product['raw_status'] ?? '') === 'active') {
            $activeListings++;
        }
    }

    foreach ($orders as $order) {
        if (in_array((string) ($order['raw_status'] ?? ''), ['pending', 'accepted', 'paid', 'ready'], true)) {
            $openOrders++;
        }

        if ((string) ($order['raw_status'] ?? '') !== 'cancelled') {
            $revenue += (float) ($order['total_amount_value'] ?? 0);
        }
    }

    return [
        ['label' => 'Listings', 'value' => (string) count($products)],
        ['label' => 'Active', 'value' => (string) $activeListings],
        ['label' => 'Open orders', 'value' => (string) $openOrders],
        ['label' => 'Revenue', 'value' => market_format_money($revenue)],
        ['label' => 'Stock units', 'value' => (string) $stockUnits],
    ];
}

function seller_save_profile(array $sellerUser, array $input, bool $requestVerification): void
{
    $capabilities = seller_capabilities();
    $pdo = db_try_get_connection();

    if (!$pdo) {
        throw new RuntimeException(market_database_unavailable_message());
    }

    if (!$capabilities['profile_support']) {
        throw new RuntimeException('Seller profile storage is still waiting for the shared seller schema.');
    }

    $businessName = trim((string) ($input['business_name'] ?? ''));
    $location = trim((string) ($input['location'] ?? ''));

    if ($businessName === '') {
        throw new RuntimeException('Enter a business or display name.');
    }

    if ($requestVerification && $location === '') {
        throw new RuntimeException('Add a trading location before requesting verification.');
    }

    $existing = seller_fetch_profile((int) $sellerUser['id']);
    $status = strtolower((string) ($existing['verification_status'] ?? 'not_requested'));

    if ($requestVerification && $status !== 'approved') {
        $status = 'pending';
    }

    $columns = [];
    $params = ['user_id' => (int) $sellerUser['id']];

    if (market_table_has_column('seller_profiles', 'business_name')) {
        $columns['business_name'] = $businessName;
    }

    if (market_table_has_column('seller_profiles', 'location')) {
        $columns['location'] = $location;
    }

    if (market_table_has_column('seller_profiles', 'verification_status')) {
        $columns['verification_status'] = $status;
    }

    if ($columns === []) {
        throw new RuntimeException('Seller profile columns are not ready yet.');
    }

    if ($existing === []) {
        $insertColumns = ['user_id'];
        $insertValues = [':user_id'];

        foreach ($columns as $column => $value) {
            $insertColumns[] = $column;
            $insertValues[] = ':' . $column;
            $params[$column] = $value;
        }

        $statement = $pdo->prepare(
            'INSERT INTO seller_profiles (' . implode(', ', $insertColumns) . ')
             VALUES (' . implode(', ', $insertValues) . ')'
        );
        $statement->execute($params);

        return;
    }

    $assignments = [];

    foreach ($columns as $column => $value) {
        $assignments[] = $column . ' = :' . $column;
        $params[$column] = $value;
    }

    $statement = $pdo->prepare(
        'UPDATE seller_profiles
         SET ' . implode(', ', $assignments) . '
         WHERE user_id = :user_id'
    );
    $statement->execute($params);
}

