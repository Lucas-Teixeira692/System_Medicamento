<?php
require_once __DIR__ . '/inc/functions.php';

ensure_data_setup();

if (current_user()) {
    redirect('dashboard.php');
}

redirect('login.php');
