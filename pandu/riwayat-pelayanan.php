<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Pandu');
$user=getUser(); $pageTitle='Riwayat Pelayanan';
try {
    $laporan=fetchAll("SELECT lp.*,s.nomor_spk,p.nomor_pkk,k.nama_kapal FROM laporan_pelayanan lp JOIN spk s ON lp.spk_id=s.id JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE lp.pandu_id=? ORDER BY lp.created_at DESC",[$user['id']]);
} catch(Exception $e){ $laporan=[]; }
if(empty($laporan)) $laporan=[
    ['id'=>1,'nomor_laporan'=>'LP-2024-001','nomor_spk'=>'SPK/001/XI/2024','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','tanggal_mulai_realisasi'=>'2024-11-16 08:00:00','tanggal_selesai_realisasi'=>'2024-11-16 10:00:00','kondisi_cuaca'=>'Cerah','status_laporan'=>'Disetujui','created_at'=>'2024-11-16 10:30:00'],
    ['id'=>2,'nomor_laporan'=>'LP-2024-002','nomor_spk'=>'SPK/002/XI/2024','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','tanggal_mulai_realisasi'=>'2024-11-15 14:00:00','tanggal_selesai_realisasi'=>'2024-11-15 16:30:00','kondisi_cuaca'=>'Berawan','status_laporan'=>'Dikirim','created_at'=>'2024-11-15 17:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div><?php backBtn('dashboard.php','Dashboard Pandu'); ?><h1 class="text-2xl font-bold mb-1">Riwayat Pelayanan</h1></div>
    <a href="buat-laporan.php" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Buat Laporan</a>
  </div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. Laporan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">SPK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Waktu Realisasi</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Cuaca</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($laporan as $l): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($l['nomor_laporan'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['nomor_spk']??'-')?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['nama_kapal'])?></td>
            <td class="py-3 px-4 text-gray-600 text-xs"><?=date('d/m/Y H:i',strtotime($l['tanggal_mulai_realisasi']??'now'))?><br><?=date('d/m/Y H:i',strtotime($l['tanggal_selesai_realisasi']??'now'))?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($l['kondisi_cuaca']??'-')?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadgeLaporan($l['status_laporan'])?> text-xs"><?=htmlspecialchars($l['status_laporan'])?></span></td>
            <td class="py-3 px-4"><a href="detail-laporan.php?id=<?=$l['id']?>" class="btn-outline text-xs py-1 px-2.5">Detail</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
