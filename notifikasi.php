<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();
$user=getUser(); $pageTitle='Notifikasi';
try {
    query("UPDATE notifikasi SET is_read=1 WHERE user_id=?",[$user['id']]);
    $notifs=fetchAll("SELECT * FROM notifikasi WHERE user_id=? ORDER BY created_at DESC LIMIT 50",[$user['id']]);
} catch(Exception $e){ $notifs=[]; }
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><h1 class="text-2xl font-bold mb-1">Notifikasi</h1><p class="text-gray-500 text-sm">Semua notifikasi untuk akun Anda</p></div>
  <div class="card overflow-hidden">
    <?php if(empty($notifs)): ?>
    <div class="p-12 text-center text-gray-500">Tidak ada notifikasi</div>
    <?php else: ?>
    <div class="divide-y divide-gray-100">
      <?php foreach($notifs as $n): ?>
      <div class="px-5 py-4">
        <div class="flex items-start gap-3">
          <div class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
          <div>
            <p class="text-sm font-medium text-gray-900"><?=htmlspecialchars($n['judul'])?></p>
            <p class="text-sm text-gray-600 mt-0.5"><?=htmlspecialchars($n['pesan'])?></p>
            <p class="text-xs text-gray-400 mt-1"><?=date('d M Y H:i',strtotime($n['created_at']))?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body></html>
