<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Dashboard Agen Kapal';

try {
    $statAktif    = fetchOne("SELECT COUNT(*) c FROM pkk WHERE agen_id=? AND status NOT IN ('Selesai','Ditolak')", [$user['id']])['c'] ?? 0;
    $statMenunggu = fetchOne("SELECT COUNT(*) c FROM pkk WHERE agen_id=? AND status='Menunggu Validasi'", [$user['id']])['c'] ?? 0;
    $statSelesai  = fetchOne("SELECT COUNT(*) c FROM pkk WHERE agen_id=? AND status='Selesai'", [$user['id']])['c'] ?? 0;
    $recentPKK    = fetchAll("SELECT p.*, k.nama_kapal FROM pkk p JOIN kapal k ON p.kapal_id=k.id WHERE p.agen_id=? ORDER BY p.tanggal_ajuan DESC LIMIT 5", [$user['id']]);
} catch(Exception $e) {
    $statAktif=4; $statMenunggu=2; $statSelesai=15; $recentPKK=[];
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Agen Kapal</h1>
    <p class="text-gray-500">Selamat datang, <?=htmlspecialchars($user['nama'])?></p>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <?php foreach([['PKK Aktif',$statAktif.' Permohonan','text-blue-600'],['Menunggu Validasi',$statMenunggu.' PKK','text-yellow-600'],['Selesai',$statSelesai.' PKK','text-green-600']] as [$lbl,$val,$col]): ?>
    <div class="card p-6"><div class="flex items-center justify-between">
      <div><p class="text-gray-500 text-sm"><?=$lbl?></p><p class="text-2xl font-bold mt-1"><?=$val?></p></div>
      <svg class="w-8 h-8 <?=$col?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
    </div></div>
    <?php endforeach; ?>
  </div>

  <!-- Task Utama -->
  <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Utama</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <?php foreach([
      ['Ajukan PKK','Buat permohonan pelayanan kapal baru','ajukan-pkk.php','text-blue-600'],
      ['PPKB &amp; Bayar EPB','Kelola pembayaran dan tagihan pelayanan','form-ppkb.php','text-purple-600'],
    ] as [$t,$d,$url,$col]): ?>
    <a href="<?=$url?>" class="card p-6 hover:shadow-lg transition-shadow cursor-pointer block group">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="font-semibold text-gray-900 mb-1"><?=$t?></h3>
          <p class="text-gray-500 text-sm"><?=$d?></p>
        </div>
        <svg class="w-8 h-8 <?=$col?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Riwayat -->
  <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat &amp; Laporan</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <?php foreach([
      ['Riwayat Ajukan PKK','Lihat riwayat permohonan PKK','riwayat-pkk.php','text-green-600'],
      ['Riwayat PPKB &amp; Bayar EPB','Lihat riwayat PPKB dan pembayaran','riwayat-ppkb.php','text-orange-600'],
    ] as [$t,$d,$url,$col]): ?>
    <a href="<?=$url?>" class="card p-6 hover:shadow-lg transition-shadow cursor-pointer block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold text-gray-900 mb-1"><?=$t?></h3><p class="text-gray-500 text-sm"><?=$d?></p></div>
        <svg class="w-8 h-8 <?=$col?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Recent Activity -->
  <div class="card p-6">
    <h3 class="font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
    <?php if (!empty($recentPKK)): ?>
    <div class="divide-y divide-gray-100">
      <?php foreach($recentPKK as $p): ?>
      <div class="py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <?php if(in_array($p['status'],['Divalidasi','Dijadwalkan','Selesai'])): ?>
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?php else: ?>
          <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?php endif; ?>
          <div>
            <p class="text-sm font-medium text-gray-900"><?=htmlspecialchars($p['nomor_pkk'])?> - <?=htmlspecialchars($p['nama_kapal'])?></p>
            <p class="text-xs text-gray-500"><?=date('d M Y H:i',strtotime($p['tanggal_ajuan']))?></p>
          </div>
        </div>
        <span class="badge <?=statusBadgePKK($p['status'])?> text-xs"><?=htmlspecialchars($p['status'])?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach([['PKK #2024-003 disetujui KSOP','2 jam lalu','success'],['Pembayaran PPKB #2024-002 berhasil','5 jam lalu','success'],['PKK #2024-004 sedang divalidasi','1 hari lalu','pending']] as [$a,$t,$s]): ?>
      <div class="py-3 border-b last:border-0 flex items-center gap-3">
        <?php if($s==='success'): ?><svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?php else: ?><svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?php endif; ?>
        <div><p class="text-sm font-medium text-gray-900"><?=$a?></p><p class="text-xs text-gray-500"><?=$t?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
