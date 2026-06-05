<?php

require __DIR__ . '/includes/app.php';

$currentUser = app_require_login();
$mode = (string) ($_GET['mode'] ?? 'buyer');

app_set_account_mode($mode);
app_set_flash('success', 'Switched to ' . (app_account_mode() === 'seller' ? 'seller' : 'buyer') . ' view.');

if (app_account_mode() === 'seller') {
    app_redirect('seller-dashboard.php');
}

app_redirect('buyer-dashboard.php');
