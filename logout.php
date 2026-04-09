<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
session_destroy();
header('Location: ' . url('index.php'));
exit;
