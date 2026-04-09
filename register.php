<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

$pageTitle = 'Register - SIM Pelayanan Kapal';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Validasi
    if (!$username || !$password || !$password_confirm || !$nama_lengkap || !$role) {
        $error = 'Username, password, nama lengkap, dan role harus diisi';
    } elseif (strlen($username) < 4) {
        $error = 'Username harus minimal 4 karakter';
    } elseif (strlen($password) < 6) {
        $error = 'Password harus minimal 6 karakter';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak sesuai';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Cek username sudah ada
        $check = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($check) {
            $error = 'Username sudah terdaftar';
        } else {
            // Cek email sudah ada (jika email diisi)
            if ($email) {
                $checkEmail = fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
                if ($checkEmail) {
                    $error = 'Email sudah terdaftar';
                }
            }
        }
    }

    if (!$error) {
        try {
            // Dapatkan role_id berdasarkan nama_role
            $roleData = fetchOne("SELECT id FROM roles WHERE nama_role = ?", [$role]);
            if (!$roleData) {
                $error = 'Role tidak ditemukan';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user baru
                query(
                    "INSERT INTO users (username, password, nama_lengkap, email, no_telepon, role_id, is_active) 
                     VALUES (?, ?, ?, ?, ?, ?, 1)",
                    [$username, $hashedPassword, $nama_lengkap, $email ?: null, $no_telepon ?: null, $roleData['id']]
                );
                
                $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                // Clear form
                $_POST = [];
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat registrasi: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center p-4 py-8">
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
        <h2 class="text-xl font-bold text-gray-900">Daftar Akun Baru</h2>
        <p class="text-gray-500 text-sm mt-1">Buat akun untuk mengakses sistem</p>
      </div>
      <?php if ($error): ?>
      <div class="alert-error mb-4">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm"><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-success mb-4">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        <span class="text-sm"><?= htmlspecialchars($success) ?></span>
      </div>
      <div class="text-center mt-4">
        <a href="<?= url('login.php') ?>" class="btn-primary justify-center py-2.5 text-sm font-medium">Login Sekarang</a>
      </div>
      <?php else: ?>
      <form method="post" class="space-y-4">
        <div>
          <label class="form-label">Username</label>
          <input type="text" name="username" placeholder="Minimal 4 karakter" class="form-input" required value="<?=htmlspecialchars($_POST['username']??'')?>">
          <p class="text-xs text-gray-500 mt-1">Gunakan huruf, angka, dan underscore</p>
        </div>
        <div>
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" class="form-input" required value="<?=htmlspecialchars($_POST['nama_lengkap']??'')?>">
        </div>
        <div>
          <label class="form-label">Email (Opsional)</label>
          <input type="email" name="email" placeholder="Masukkan email" class="form-input" value="<?=htmlspecialchars($_POST['email']??'')?>">
        </div>
        <div>
          <label class="form-label">No Telepon (Opsional)</label>
          <input type="tel" name="no_telepon" placeholder="Contoh: 0812xxxx" class="form-input" value="<?=htmlspecialchars($_POST['no_telepon']??'')?>">
        </div>
        <div>
          <label class="form-label">Password</label>
          <input type="password" name="password" placeholder="Minimal 6 karakter" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Konfirmasi Password</label>
          <input type="password" name="password_confirm" placeholder="Ulangi password" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Role</label>
          <select name="role" class="form-input" required>
            <option value="">Pilih role</option>
            <?php foreach(['Agen Kapal','KSOP','Planner','Pandu'] as $r): ?>
            <option value="<?=$r?>" <?=($_POST['role']??'')==$r?'selected':''?>><?=$r?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm font-medium">Daftar</button>
      </form>
      <?php endif; ?>
    </div>
    <div class="text-center mt-6">
      <p class="text-sm text-gray-600">Sudah punya akun? <a href="<?= url('login.php') ?>" class="text-blue-600 hover:underline font-medium">Login di sini</a></p>
    </div>
  </div>
</div>
</body></html>
