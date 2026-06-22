<?php

function app_session_path(): string
{
    $sessionPath = dirname(__DIR__) . '/tmp/sessions';

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }

    return $sessionPath;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_save_path(app_session_path());
    session_start();
}

function app_hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function app_verify_password(string $password, string $storedHash): bool
{
    if (strpos($storedHash, 'pbkdf2_sha256$') !== 0) {
        return password_verify($password, $storedHash);
    }

    $parts = explode('$', $storedHash);
    if (count($parts) !== 4) {
        return false;
    }

    $iterations = (int) $parts[1];
    $salt = base64_decode($parts[2], true);
    if ($iterations < 1 || $salt === false) {
        return false;
    }

    $digest = base64_encode(hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true));

    return hash_equals($parts[3], $digest);
}

function app_is_post_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function app_set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function app_pull_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key]) || !is_string($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function app_login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user_id'] = (int) $user['id'];
}

function app_logout_user(): void
{
    unset($_SESSION['auth_user_id']);
    unset($_SESSION['account_mode']);
    session_regenerate_id(true);
}

function app_current_user(): ?array
{
    static $loaded = false;
    static $user = null;

    if ($loaded) {
        return $user;
    }

    $loaded = true;

    if (!isset($_SESSION['auth_user_id'])) {
        return null;
    }

    $user = market_get_user_by_id((int) $_SESSION['auth_user_id']);

    if ($user === null || !app_user_can_login($user)) {
        unset($_SESSION['auth_user_id']);
        $user = null;
    }

    return $user;
}

function app_user_role(?array $user): string
{
    if ($user === null) {
        return 'guest';
    }

    return market_normalize_role($user);
}

function app_user_can_login(array $user): bool
{
    return (string) ($user['status'] ?? 'active') === 'active';
}

function app_is_admin(?array $user): bool
{
    return app_user_role($user) === 'admin';
}

function app_is_seller(?array $user): bool
{
    return app_user_role($user) === 'seller';
}

function app_is_buyer(?array $user): bool
{
    return app_user_role($user) === 'buyer';
}

function app_account_mode(): string
{
    return app_user_role(app_current_user());
}

function app_set_account_mode(string $mode): void
{
    $_SESSION['account_mode'] = $mode;
}

function app_dashboard_path_for_user(array $user): string
{
    $role = app_user_role($user);

    if ($role === 'admin') {
        return 'admin/dashboard.php';
    }

    if ($role === 'seller') {
        return 'seller-dashboard.php';
    }

    return 'buyer-dashboard.php';
}

function app_redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function app_require_login(): array
{
    $user = app_current_user();

    if ($user !== null) {
        return $user;
    }

    app_set_flash('error', 'Please sign in to continue.');
    app_redirect('login.php');
}

function app_require_admin(): array
{
    $user = app_require_login();

    if (app_is_admin($user)) {
        return $user;
    }

    app_set_flash('error', 'Admin access is required for that page.');
    app_redirect(app_dashboard_path_for_user($user));
}

function app_require_seller(): array
{
    $user = app_require_login();

    if (app_is_seller($user)) {
        return $user;
    }

    app_set_flash('error', 'Seller access is required for that page.');
    app_redirect(app_dashboard_path_for_user($user));
}

function app_require_buyer(): array
{
    $user = app_require_login();

    if (app_is_buyer($user)) {
        return $user;
    }

    app_set_flash('error', 'Buyer access is required for that page.');
    app_redirect(app_dashboard_path_for_user($user));
}
