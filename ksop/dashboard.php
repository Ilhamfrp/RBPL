<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user = getUser();
$pageTitle = 'Dashboard KSOP';
try {
    $statMasuk   = fetchOne("SELECT COUNT(*) c FROM pkk")['c']??0;
    $statMenunggu= fetchOne("SELECT COUNT(*) c FROM pkk WHERE status='Menunggu Validasi'")['c']??0;
    $statValid   = fetchOne("SELECT COUNT(*) c FROM pkk WHERE status IN ('Divalidasi','Dijadwalkan','Selesai')")['c']??0;
    $statDokumen = fetchOne("SELECT COUNT(*) c FROM dokumen_resmi")['c']??0;
} catch(Exception $e){ $statMasuk=8;$statMenunggu=3;$statValid=12;$statDokumen=15; }
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Dashboard KSOP</h1>
    <p class="text-gray-500">Selamat datang, <?=htmlspecialchars($user['nama'])?></p>
  </div>
  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php foreach([['PKK Masuk',$statMasuk.' Permohonan','text-blue-600'],['Menunggu Validasi',$statMenunggu.' PKK','text-yellow-600'],['PKK Tervalidasi',$statValid.' PKK','text-green-600'],['Dokumen Terbit',$statDokumen.' Dokumen','text-indigo-600']] as [$l,$v,$c]): ?>
    <div class="card p-6"><div class="flex items-center justify-between">
      <div><p class="text-gray-500 text-sm"><?=$l?></p><p class="text-2xl font-bold mt-1"><?=$v?></p></div>
      <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    </div></div>
    <?php endforeach; ?>
  </div>
  <!-- Task Utama -->
  <h2 class="text-lg font-semibold mb-4">Task Utama</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <?php foreach([
      ['Validasi PKK &amp; Dokumen','Periksa kelengkapan dokumen PKK dari Agen Kapal','daftar-pkk.php','text-green-600'],
      ['Terbitkan Dokumen Resmi','Cek &amp; terbitkan dokumen resmi (PKK, PPKB, Jadwal, Alokasi)','terbitkan-dokumen.php','text-indigo-600'],
      ['Tinjauan &amp; Simpan Laporan','Tinjau laporan dari Pandu dan simpan atau minta revisi','peninjauan-laporan.php','text-yellow-600'],
    ] as [$t,$d,$url,$c]): ?>
    <a href="<?=$url?>" class="card p-6 hover:shadow-lg transition-shadow block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold text-gray-900 mb-1"><?=$t?></h3><p class="text-gray-500 text-sm"><?=$d?></p></div>
        <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
    <?php endforeach; ?>
    <a href="riwayat-laporan.php" class="card p-6 hover:shadow-lg transition-shadow block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold text-gray-900 mb-1">Riwayat Laporan Tersimpan</h3><p class="text-gray-500 text-sm">Lihat semua laporan yang telah disimpan</p></div>
        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
  </div>
  <!-- Recent Activity -->
  <div class="card p-6">
    <h3 class="font-semibold mb-4">Aktivitas Terbaru</h3>
    <div class="space-y-4">
      <?php foreach([['PKK #2024-004 divalidasi - Valid','1 jam lalu','success'],['Dokumen Resmi SPK #001 diterbitkan','2 jam lalu','success'],['Laporan Pandu #002 perlu revisi','5 jam lalu','warning']] as [$a,$t,$s]): ?>
      <div class="py-3 border-b last:border-0 flex items-center gap-3">
        <?php if($s==='success'): ?><svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?php else: ?><svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?php endif; ?>
        <div><p class="text-sm font-medium"><?=$a?></p><p class="text-xs text-gray-500"><?=$t?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
