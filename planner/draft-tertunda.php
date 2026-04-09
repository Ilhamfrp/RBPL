<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Planner');
$user=getUser(); $pageTitle='Draft Tertunda';
try {
    $drafts=fetchAll("SELECT j.*,p.nomor_pkk,k.nama_kapal,d.kode_dermaga FROM jadwal_pelayanan j JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id LEFT JOIN dermaga d ON j.dermaga_id=d.id WHERE j.status='Ditunda' ORDER BY j.created_at DESC");
} catch(Exception $e){ $drafts=[]; }
if(empty($drafts)) $drafts=[
    ['id'=>1,'nomor_pkk'=>'PKK-2024-005','nama_kapal'=>'MV Maritime Express','kode_dermaga'=>'D-01','tanggal_mulai'=>'2024-11-17 07:00:00','status'=>'Ditunda','catatan'=>'Menunggu konfirmasi dari agen','created_at'=>'2024-11-13 10:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Dashboard Planner'); ?>
    <h1 class="text-2xl font-bold mb-1">Draft Pelayanan Tertunda</h1>
    <p class="text-gray-500 text-sm">Draft jadwal yang belum dikonfirmasi atau menunggu tindak lanjut</p></div>
  <?php if(empty($drafts)): ?>
  <div class="card p-12 text-center">
    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p class="text-gray-500">Tidak ada draft yang tertunda</p>
  </div>
  <?php else: ?>
  <div class="space-y-4">
    <?php foreach($drafts as $dr): ?>
    <div class="card p-6">
      <div class="flex items-start justify-between mb-3">
        <div>
          <p class="font-semibold"><?=htmlspecialchars($dr['nomor_pkk'])?> - <?=htmlspecialchars($dr['nama_kapal'])?></p>
          <p class="text-sm text-gray-500 mt-0.5">Dermaga: <?=htmlspecialchars($dr['kode_dermaga']??'-')?> &bull; <?=date('d/m/Y H:i',strtotime($dr['tanggal_mulai']))?></p>
        </div>
        <span class="badge bg-yellow-100 text-yellow-800 text-xs">Ditunda</span>
      </div>
      <?php if($dr['catatan']??''): ?><p class="text-sm text-gray-600 bg-yellow-50 p-3 rounded-lg mt-2"><?=htmlspecialchars($dr['catatan'])?></p><?php endif; ?>
      <div class="mt-4 flex gap-2">
        <a href="susun-jadwal.php?pkk_id=<?=$dr['pkk_id']??0?>" class="btn-primary text-xs py-1.5 px-3">Lanjutkan Susun Jadwal</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
