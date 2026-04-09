<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Planner');
$user=getUser(); $pageTitle='PKK Tervalidasi';
try {
    $pkks=fetchAll("SELECT p.*, k.nama_kapal, k.jenis_kapal, u.nama_lengkap as nama_agen FROM pkk p JOIN kapal k ON p.kapal_id=k.id JOIN users u ON p.agen_id=u.id WHERE p.status='Divalidasi' ORDER BY p.tanggal_ajuan DESC");
} catch(Exception $e){ $pkks=[]; }
if(empty($pkks)) $pkks=[
    ['id'=>3,'nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_kapal'=>'Kontainer','nama_agen'=>'Budi Santoso','jenis_layanan'=>'Kedatangan','tanggal_kedatangan'=>'2024-11-18','dermaga_diminta'=>'D-02','status'=>'Divalidasi','tanggal_ajuan'=>'2024-11-12 10:00:00'],
    ['id'=>5,'nomor_pkk'=>'PKK-2024-005','nama_kapal'=>'MV Maritime Express','jenis_kapal'=>'Kontainer','nama_agen'=>'Ahmad Rizki','jenis_layanan'=>'Kedatangan','tanggal_kedatangan'=>'2024-11-17','dermaga_diminta'=>'D-01','status'=>'Divalidasi','tanggal_ajuan'=>'2024-11-13 09:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div><?php backBtn('dashboard.php','Dashboard Planner'); ?><h1 class="text-2xl font-bold mb-1">PKK Tervalidasi - Siap Dijadwalkan</h1></div>
  </div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Agen</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis Layanan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tgl Kedatangan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Dermaga</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($pkks as $p): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($p['nomor_pkk'])?></td>
            <td class="py-3 px-4"><div class="font-medium"><?=htmlspecialchars($p['nama_kapal'])?></div><div class="text-xs text-gray-500"><?=htmlspecialchars($p['jenis_kapal'])?></div></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['nama_agen'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['jenis_layanan'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['tanggal_kedatangan']??'-')?></td>
            <td class="py-3 px-4"><span class="badge bg-blue-50 text-blue-700 border border-blue-200"><?=htmlspecialchars($p['dermaga_diminta']??'-')?></span></td>
            <td class="py-3 px-4"><a href="susun-jadwal.php?pkk_id=<?=$p['id']?>" class="btn-primary text-xs py-1 px-2.5">Susun Jadwal</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
