<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
$user = getUser();
$notifCount = $user ? countUnreadNotif() : 0;
$notifs = [];
if ($user) {
    try { $notifs = fetchAll("SELECT * FROM notifikasi WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$user['id']]); }
    catch(Exception $e) { $notifs = []; }
}
?>
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">

      <!-- Logo -->
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/>
          <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/>
          <path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/>
          <path d="M12 10v4"/>
          <path d="M12 3V1"/>
        </svg>
        <div>
          <span class="font-bold text-blue-900 text-base leading-tight block">SIM Pelayanan Kapal</span>
          <?php if ($user): ?>
          <span class="text-gray-500 text-xs block"><?= htmlspecialchars($user['role']) ?></span>
          <?php endif; ?>
        </div>
      </a>

      <!-- Right -->
      <div class="flex items-center gap-2">
        <?php if ($user): ?>

          <!-- Notifikasi -->
          <div class="relative" x-data="{open:false}">
            <button @click="open=!open" class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              <?php if ($notifCount > 0): ?>
              <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"><?= $notifCount ?></span>
              <?php endif; ?>
            </button>
            <div x-show="open" @click.away="open=false" x-cloak
                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50">
              <div class="px-4 py-3 bg-gray-50 border-b"><span class="font-semibold text-sm">Notifikasi</span></div>
              <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                <?php if (empty($notifs)): ?>
                <div class="px-4 py-6 text-center text-gray-400 text-sm">Tidak ada notifikasi</div>
                <?php else: foreach ($notifs as $n): ?>
                <div class="px-4 py-3 hover:bg-gray-50 <?= $n['is_read'] ? '' : 'bg-blue-50' ?>">
                  <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($n['judul']) ?></p>
                  <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($n['pesan']) ?></p>
                  <p class="text-xs text-gray-400 mt-1"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></p>
                </div>
                <?php endforeach; endif; ?>
              </div>
              <div class="px-4 py-2 bg-gray-50 border-t">
                <a href="<?= url('notifikasi.php') ?>" class="text-blue-600 text-xs hover:underline">Lihat semua</a>
              </div>
            </div>
          </div>

          <!-- User menu -->
          <div class="relative" x-data="{open:false}">
            <button @click="open=!open" class="flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="hidden sm:block"><?= htmlspecialchars($user['username']) ?></span>
            </button>
            <div x-show="open" @click.away="open=false" x-cloak
                 class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50">
              <div class="px-4 py-3 bg-gray-50 border-b">
                <p class="font-semibold text-sm"><?= htmlspecialchars($user['username']) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($user['role']) ?></p>
              </div>
              <a href="<?= getDashboardUrl() ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
              </a>
              <div class="border-t"></div>
              <a href="<?= url('logout.php') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
              </a>
            </div>
          </div>

        <?php else: ?>
          <!-- ===== LOGIN: Direct link + dropdown untuk pilih role ===== -->

          <!-- Tombol Login langsung (selalu terlihat, tidak butuh JS) -->
          <a href="<?= url('login.php') ?>"
             class="text-blue-700 font-medium text-sm px-3 py-2 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
            Login
          </a>

          <!-- Dropdown pilih role (pakai Alpine, opsional) -->
          <div class="relative" x-data="{open:false}">
            <button @click="open=!open"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-1">
              Masuk sebagai
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" @click.away="open=false" x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50">
              <?php foreach (['Agen Kapal','KSOP','Planner','Pandu'] as $role): ?>
              <a href="<?= url('login.php') ?>?role=<?= urlencode($role) ?>"
                 class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <?= $role ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
