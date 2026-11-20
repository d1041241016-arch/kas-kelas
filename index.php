<?php
require_once __DIR__ . '/includes/auth.php';
redirect(isset($_SESSION['user_id']) ? '/dashboard.php' : '/login.php');
