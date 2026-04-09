<?php
// includes/header.php
// $pageTitle is set by the including page
// $user is the current user array or null
$notifCount = isLoggedIn() ? getNotifCount($_SESSION['user_id']) : 0;
$notifications = isLoggedIn() ? getNotifications($_SESSION['user_id']) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'SIM Pelayanan Kapal'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --font-size: 16px;
            --background: #ffffff;
            --foreground: #0f0f23;
            --card: #ffffff;
            --primary: #030213;
            --primary-foreground: #ffffff;
            --muted: #ececf0;
            --muted-foreground: #717182;
            --accent: #e9ebef;
            --border: rgba(0,0,0,0.1);
            --radius: 0.625rem;
            --destructive: #d4183d;
        }
        body { font-size: var(--font-size); color: var(--foreground); background: #f9fafb; }
        h1 { font-size: 1.5rem; font-weight: 500; line-height: 1.5; }
        h2 { font-size: 1.25rem; font-weight: 500; line-height: 1.5; }
        h3 { font-size: 1.125rem; font-weight: 500; line-height: 1.5; }
        h4 { font-size: 1rem; font-weight: 500; line-height: 1.5; }
        .card { background: white; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.9rem; cursor: pointer; transition: all 0.15s; border: 1px solid transparent; }
        .btn-primary { background: #2563eb; color: white; } .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: white; color: #374151; border-color: #d1d5db; } .btn-outline:hover { background: #f9fafb; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .btn-lg { padding: 0.75rem 1.5rem; font-size: 1rem; }
        .btn-danger { background: #dc2626; color: white; } .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: #16a34a; color: white; } .btn-success:hover { background: #15803d; }
        .badge { display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; background: white; transition: border-color 0.15s; }
        .input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
        .label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid; display: flex; gap: 0.75rem; align-items: flex-start; }
        .alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .alert-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-menu { position: absolute; right: 0; top: 100%; z-index: 50; min-width: 200px; background: white; border: 1px solid #e5e7eb; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: none; margin-top: 0.25rem; }
        .dropdown:hover .dropdown-menu, .dropdown-menu.show { display: block; }
        .dropdown-item { display: block; padding: 0.5rem 1rem; font-size: 0.875rem; color: #374151; text-decoration: none; transition: background 0.1s; }
        .dropdown-item:hover { background: #f3f4f6; }
        select.input { appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem; }
        textarea.input { resize: vertical; min-height: 80px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        td { padding: 0.75rem 1rem; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:hover td { background: #f9fafb; }
        .page-wrapper { min-height: 100vh; background: #f9fafb; }
        .main-content { max-width: 1440px; margin: 0 auto; padding: 2rem 1.5rem; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center; }
        .modal-backdrop.active { display: flex; }
        .modal { background: white; border-radius: 0.625rem; padding: 1.5rem; max-width: 480px; width: 90%; position: relative; }
        @media (max-width: 768px) { .main-content { padding: 1rem; } }
    </style>
</head>
<body>
<div class="page-wrapper">
<!-- Navbar -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-ship text-blue-600 text-2xl"></i>
                <div>
                    <a href="<?= url('index.php') ?>" class="text-blue-900 font-semibold hover:text-blue-700 no-underline">SIM Pelayanan Kapal</a>
                    <?php if (isLoggedIn()): ?>
                    <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php if (isLoggedIn()): ?>
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-outline btn-sm relative" style="border:none;background:transparent;padding:0.5rem;">
                        <i class="fa-regular fa-bell text-gray-600 text-lg"></i>
                        <?php if ($notifCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $notifCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu" style="min-width:300px;">
                        <div class="px-4 py-2 border-b font-semibold text-sm">Notifikasi</div>
                        <div style="max-height:300px;overflow-y:auto;">
                            <?php if (empty($notifications)): ?>
                            <div class="px-4 py-3 text-gray-500 text-sm">Tidak ada notifikasi</div>
                            <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                            <a href="<?= url('notifikasi.php') ?>" class="dropdown-item">
                                <div class="font-medium text-sm"><?php echo htmlspecialchars($notif['judul']); ?></div>
                                <div class="text-gray-500 text-xs mt-1"><?php echo htmlspecialchars($notif['pesan']); ?></div>
                                <div class="text-gray-400 text-xs mt-1"><?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?></div>
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline btn-sm" style="border:none;background:transparent;padding:0.5rem;">
                        <i class="fa-regular fa-circle-user text-gray-600 text-lg"></i>
                    </button>
                    <div class="dropdown-menu">
                        <div class="px-4 py-3 border-b">
                            <div class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                        </div>
                        <a href="<?= url('logout.php') ?>" class="dropdown-item text-red-600">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="dropdown">
                    <button class="btn btn-primary">Login</button>
                    <div class="dropdown-menu">
                        <a href="<?= url('login.php') ?>?role=Agen+Kapal" class="dropdown-item">Agen Kapal</a>
                        <a href="<?= url('login.php') ?>?role=KSOP" class="dropdown-item">KSOP</a>
                        <a href="<?= url('login.php') ?>?role=Planner" class="dropdown-item">Planner</a>
                        <a href="<?= url('login.php') ?>?role=Pandu" class="dropdown-item">Pandu</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<!-- End Navbar -->
