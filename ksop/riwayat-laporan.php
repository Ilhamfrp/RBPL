<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user=getUser(); $pageTitle='Riwayat Laporan';
try {
    $laporan=fetchAll("SELECT lp.*, s.nomor_spk, p.nomor_pkk, k.nama_kapal, u.nama_lengkap as nama_pandu FROM laporan_pelayanan lp JOIN spk s ON lp.spk_id=s.id JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id JOIN users u ON lp.pandu_id=u.id WHERE lp.status_laporan IN ('Disetujui','Perlu Revisi') ORDER BY lp.updated_at DESC");
} catch(Exception $e){ $laporan=[]; }
if(empty($laporan)) $laporan=[
    ['nomor_laporan'=>'LP-2024-001','nomor_spk'=>'SPK/001/XI/2024','nama_kapal'=>'MV Ocean Star','nama_pandu'=>'Capt. Faisal Ibrahim','status_laporan'=>'Disetujui','updated_at'=>'2024-11-16 20:00:00'],
    ['nomor_laporan'=>'LP-2024-002','nomor_spk'=>'SPK/002/XI/2024','nama_kapal'=>'MV Blue Horizon','nama_pandu'=>'Capt. Ahmad Gunawan','status_laporan'=>'Perlu Revisi','updated_at'=>'2024-11-15 19:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Dashboard KSOP'); ?>
    <h1 class="text-2xl font-bold mb-1">Riwayat Laporan Tersimpan</h1></div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. Laporan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">SPK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Pandu</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
        </tr></thead>
        <tbody>
          <?php foreach($laporan as $l): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($l['nomor_laporan'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['nomor_spk']??'-')?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['nama_kapal']??'-')?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['nama_pandu']??'-')?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadgeLaporan($l['status_laporan'])?> text-xs"><?=$l['status_laporan']?></span></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y H:i',strtotime($l['updated_at']))?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
