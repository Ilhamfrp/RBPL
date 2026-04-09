<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

$pageTitle = 'Login - SIM Pelayanan Kapal';
$error = '';
$selectedRole = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if (!$username || !$password || !$role) {
        $error = 'Semua field harus diisi';
    } else {
        $u = fetchOne("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.is_active = 1", [$username]);
        if ($u && password_verify($password, $u['password']) && $u['nama_role'] === $role) {
            $_SESSION['user_id']  = $u['id'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['nama']     = $u['nama_lengkap'];
            $_SESSION['role']     = $u['nama_role'];
            $_SESSION['role_id']  = $u['role_id'];
            header('Location: ' . getDashboardUrl($role));
            exit;
        } else {
            $error = 'Username, password, atau role tidak sesuai';
        }
    }
}
include __DIR__ . '/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-3 mb-4">
        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
        <div class="text-left">
          <h1 class="text-blue-900 font-bold text-xl leading-tight">SIM Pelayanan Kapal</h1>
          <p class="text-gray-600 text-sm">Sistem Manajemen Pelabuhan</p>
        </div>
      </a>
    </div>
    <div class="card p-8">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Login ke Sistem</h2>
        <p class="text-gray-500 text-sm mt-1"><?= $selectedRole ? 'Login sebagai '.$selectedRole : 'Masukkan kredensial Anda untuk mengakses sistem' ?></p>
      </div>
      <?php if ($error): ?>
      <div class="alert-error mb-4">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm"><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <div>
          <label class="form-label">Username</label>
          <input type="text" name="username" placeholder="Masukkan username" class="form-input" required value="<?=htmlspecialchars($_POST['username']??'')?>">
        </div>
        <div>
          <label class="form-label">Password</label>
          <input type="password" name="password" placeholder="Masukkan password" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Role</label>
          <select name="role" class="form-input" required>
            <option value="">Pilih role</option>
            <?php foreach(['Agen Kapal','KSOP','Planner','Pandu'] as $r): ?>
            <option value="<?=$r?>" <?=($selectedRole==$r||($_POST['role']??'')==$r)?'selected':''?>><?=$r?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm font-medium">Login</button>
        <div class="text-center">
          <p class="text-xs text-gray-400">Demo: username: <b>agen1</b> / password: <b>password</b> / role: <b>Agen Kapal</b></p>
          <p class="text-xs text-gray-400 mt-1">KSOP: <b>ksop1</b> | Planner: <b>planner1</b> | Pandu: <b>pandu1</b></p>
        </div>
      </form>
    </div>
    <div class="text-center mt-6 space-y-2">
      <p class="text-sm text-gray-600">Belum punya akun? <a href="<?= url('register.php') ?>" class="text-blue-600 hover:underline font-medium">Daftar di sini</a></p>
      <a href="<?= url('index.php') ?>" class="block text-blue-600 hover:underline text-sm">Kembali ke Beranda</a>
    </div>
  </div>
</div>
</body></html>
