<?php

require __DIR__ . '/includes/app.php';

$currentUser = app_current_user();
app_set_flash('success', 'The seller workflow was removed from this simplified version.');
app_redirect($currentUser && app_is_admin($currentUser) ? 'admin/dashboard.php' : 'buyer-dashboard.php');
