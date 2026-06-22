<?php

require __DIR__ . '/includes/app.php';

$currentUser = app_require_login();
app_set_flash('success', 'Your account view follows the role saved on your profile.');
app_redirect(app_dashboard_path_for_user($currentUser));
