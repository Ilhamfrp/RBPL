<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Riwayat PKK';

try {
    $pkks = fetchAll("SELECT p.*, k.nama_kapal, k.jenis_kapal FROM pkk p JOIN kapal k ON p.kapal_id=k.id WHERE p.agen_id=? ORDER BY p.tanggal_ajuan DESC", [$user['id']]);
} catch(Exception $e) { $pkks = []; }

// Sample data fallback
if (empty($pkks)) {
    $pkks = [
        ['nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','jenis_layanan'=>'Kedatangan','dermaga_diminta'=>'D-01','tanggal_ajuan'=>'2024-11-14 09:00:00','status'=>'Dijadwalkan'],
        ['nomor_pkk'=>'PKK-2024-002','nama_kapal'=>'MV Pacific Dream','jenis_kapal'=>'Bulk Carrier','jenis_layanan'=>'Keberangkatan','dermaga_diminta'=>'D-03','tanggal_ajuan'=>'2024-11-13 14:00:00','status'=>'Divalidasi'],
        ['nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_kapal'=>'Kontainer','jenis_layanan'=>'Kedatangan','dermaga_diminta'=>'D-02','tanggal_ajuan'=>'2024-11-10 10:30:00','status'=>'Selesai'],
    ];
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Riwayat Ajuan PKK</h1>
      <p class="text-gray-500 text-sm">Semua permohonan kunjungan kapal yang pernah diajukan</p>
    </div>
    <a href="ajukan-pkk.php" class="btn-primary">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Ajukan PKK Baru
    </a>
  </div>

  <div class="card overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
      <span class="text-sm font-medium text-gray-700">Total: <?=count($pkks)?> PKK</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Nama Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis Layanan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Dermaga</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal Ajuan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($pkks as $p): ?>
          <tr class="border-b hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($p['nomor_pkk'])?></td>
            <td class="py-3 px-4">
              <div class="font-medium"><?=htmlspecialchars($p['nama_kapal'])?></div>
              <div class="text-xs text-gray-500"><?=htmlspecialchars($p['jenis_kapal'])?></div>
            </td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['jenis_layanan'])?></td>
            <td class="py-3 px-4"><span class="badge bg-blue-50 text-blue-700 border border-blue-200"><?=htmlspecialchars($p['dermaga_diminta']??'-')?></span></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y H:i',strtotime($p['tanggal_ajuan']))?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadgePKK($p['status'])?>"><?=htmlspecialchars($p['status'])?></span></td>
            <td class="py-3 px-4"><a href="status-pkk.php?id=<?=$p['id']??0?>" class="btn-outline text-xs py-1 px-2.5">Detail</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
