<?php

require __DIR__ . '/includes/app.php';

$currentUser = app_require_login();

app_set_account_mode('buyer');
app_set_flash('success', 'Your account view is based on your login role.');
app_redirect(app_dashboard_path_for_user($currentUser));
