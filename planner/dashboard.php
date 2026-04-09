<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Planner');
$user=getUser(); $pageTitle='Dashboard Planner';
try{
    $statValid=fetchOne("SELECT COUNT(*) c FROM pkk WHERE status='Divalidasi'")['c']??0;
    $statPerlu=fetchOne("SELECT COUNT(*) c FROM pkk p LEFT JOIN jadwal_pelayanan j ON j.pkk_id=p.id WHERE p.status='Divalidasi' AND j.id IS NULL")['c']??0;
    $statAktif=fetchOne("SELECT COUNT(*) c FROM jadwal_pelayanan WHERE status IN ('Dikonfirmasi','Berlangsung')")['c']??0;
    $statBentrok=0;
}catch(Exception $e){$statValid=5;$statPerlu=2;$statAktif=8;$statBentrok=0;}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-8">
    <h1 class="text-2xl font-bold mb-1">Dashboard Planner</h1>
    <p class="text-gray-500">Selamat datang, <?=htmlspecialchars($user['nama'])?></p>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php foreach([['PKK Valid',$statValid.' Permohonan','text-blue-600'],['Perlu Dijadwalkan',$statPerlu.' PKK','text-yellow-600'],['Jadwal Aktif',$statAktif.' Jadwal','text-green-600'],['Bentrok',$statBentrok.' Konflik','text-gray-400']] as [$l,$v,$c]): ?>
    <div class="card p-6"><div class="flex items-center justify-between">
      <div><p class="text-gray-500 text-sm"><?=$l?></p><p class="text-2xl font-bold mt-1"><?=$v?></p></div>
      <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div></div>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <?php foreach([
      ['PKK Tervalidasi','Lihat daftar PKK yang sudah divalidasi KSOP','permohonan-valid.php','text-blue-600'],
      ['Draft Perencanaan','Periksa jadwal, cek bentrok, susun draft pelayanan','susun-jadwal.php','text-green-600'],
      ['Alokasi Sumber Daya','Kelola alokasi dermaga dan pandu untuk pelayanan','alokasi-sumber-daya.php','text-purple-600'],
    ] as [$t,$d,$url,$c]): ?>
    <a href="<?=$url?>" class="card p-6 hover:shadow-lg transition-shadow block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold mb-1"><?=$t?></h3><p class="text-gray-500 text-sm"><?=$d?></p></div>
        <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
    <?php endforeach; ?>
  </div>
  <a href="draft-tertunda.php" class="card p-6 hover:shadow-lg transition-shadow block mb-8 max-w-sm">
    <div class="flex items-start justify-between mb-4">
      <div><h3 class="font-semibold mb-1">Draft Pelayanan Tertunda</h3><p class="text-gray-500 text-sm">Lihat draft yang statusnya Ditunda</p></div>
      <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <span class="btn-outline text-xs w-full justify-center">Buka</span>
  </a>
  <div class="card p-6">
    <h3 class="font-semibold mb-4">Aktivitas Terbaru</h3>
    <div class="space-y-4">
      <?php foreach([['Draft pelayanan PKK-2024-003 dikonfirmasi','2 jam lalu'],['Alokasi Pandu: Capt. Ahmad untuk MV Ocean Star','3 jam lalu'],['PKK-2024-005 ditunda - menunggu konfirmasi agen','5 jam lalu']] as [$a,$t]): ?>
      <div class="py-3 border-b last:border-0 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div><p class="text-sm font-medium"><?=$a?></p><p class="text-xs text-gray-500"><?=$t?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
