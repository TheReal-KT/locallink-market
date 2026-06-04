<?php

require __DIR__ . '/includes/app.php';

app_logout_user();
app_set_flash('success', 'You have been signed out.');
app_redirect('login.php');
