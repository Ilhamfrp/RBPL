<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'      => $_SESSION['user_id'],
        'username'=> $_SESSION['username'],
        'nama'    => $_SESSION['nama'],
        'role'    => $_SESSION['role'],
        'role_id' => $_SESSION['role_id'],
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    $user = getUser();
    if (!in_array($user['role'], (array)$roles)) {
        header('Location: ' . url('index.php'));
        exit;
    }
}

function getDashboardUrl($role = null) {
    if (!$role) { $u = getUser(); $role = $u ? $u['role'] : null; }
    switch ($role) {
        case 'Agen Kapal': return url('agen-kapal/dashboard.php');
        case 'KSOP':       return url('ksop/dashboard.php');
        case 'Planner':    return url('planner/dashboard.php');
        case 'Pandu':      return url('pandu/dashboard.php');
        default:           return url('index.php');
    }
}

function countUnreadNotif() {
    if (!isLoggedIn()) return 0;
    try {
        $r = fetchOne("SELECT COUNT(*) c FROM notifikasi WHERE user_id=? AND is_read=0", [$_SESSION['user_id']]);
        return (int)($r['c'] ?? 0);
    } catch(Exception $e) { return 0; }
}
