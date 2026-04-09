<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kapal1');
define('DB_CHARSET', 'utf8mb4');

// Auto-detect base URL (works in subfolder maupun root)
if (!defined('BASE_URL')) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = dirname($script);
    // Walk up until we find the app root (where index.php lives)
    // We detect by checking the document root depth
    $base = rtrim($dir, '/');
    // Remove the last path segment if we're inside a subfolder page
    // e.g. /kapal/agen-kapal/dashboard.php -> /kapal
    $parts = explode('/', trim($base, '/'));
    // Find 'kapal' root: remove known subfolders
    $known = ['agen-kapal','ksop','planner','pandu'];
    while (!empty($parts) && in_array(end($parts), $known)) {
        array_pop($parts);
    }
    define('BASE_URL', '/' . ltrim(implode('/', $parts), '/'));
}

function url($path = '') {
    $base = rtrim(BASE_URL, '/');
    return $base . '/' . ltrim($path, '/');
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee2e2;border:1px solid #f87171;margin:20px;border-radius:8px">
                <h3>Koneksi Database Gagal</h3><p>'.$e->getMessage().'</p>
                <p>Pastikan MySQL berjalan dan database <b>kapal1</b> sudah diimport.</p>
            </div>');
        }
    }
    return $pdo;
}

function query($sql, $params = []) {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
function fetchAll($sql, $params = []) { return query($sql, $params)->fetchAll(); }
function fetchOne($sql, $params = []) { return query($sql, $params)->fetch(); }
