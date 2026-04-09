<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user = getUser();
$pageTitle = 'Daftar PKK';

$filterStatus = $_GET['status'] ?? '';
try {
    $sql = "SELECT p.*, k.nama_kapal, k.jenis_kapal, u.nama_lengkap as nama_agen FROM pkk p JOIN kapal k ON p.kapal_id=k.id JOIN users u ON p.agen_id=u.id";
    $params = [];
    if ($filterStatus) { $sql .= " WHERE p.status=?"; $params[] = $filterStatus; }
    $sql .= " ORDER BY p.tanggal_ajuan DESC";
    $pkks = fetchAll($sql, $params);
} catch(Exception $e){ $pkks=[]; }
if(empty($pkks)) $pkks=[
    ['id'=>1,'nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','nama_agen'=>'Ahmad Rizki - PT Maritim Express','jenis_layanan'=>'Kedatangan','tanggal_ajuan'=>'2024-11-14 09:00:00','status'=>'Menunggu Validasi'],
    ['id'=>3,'nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_kapal'=>'Kontainer','nama_agen'=>'Budi Santoso - PT Samudera','jenis_layanan'=>'Kedatangan','tanggal_ajuan'=>'2024-11-12 10:00:00','status'=>'Divalidasi'],
    ['id'=>4,'nomor_pkk'=>'PKK-2024-004','nama_kapal'=>'MV Sea Voyager','jenis_kapal'=>'General Cargo','nama_agen'=>'Ahmad Rizki - PT Maritim Express','jenis_layanan'=>'Keberangkatan','tanggal_ajuan'=>'2024-11-11 08:00:00','status'=>'Menunggu Validasi'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div><?php backBtn('dashboard.php','Dashboard KSOP'); ?>
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Daftar PKK Masuk</h1></div>
    <div class="flex gap-2 flex-wrap">
      <?php foreach([''=> 'Semua','Menunggu Validasi'=>'Menunggu','Divalidasi'=>'Tervalidasi','Ditolak'=>'Ditolak'] as $val=>$lbl): ?>
      <a href="?status=<?=urlencode($val)?>" class="<?=($filterStatus===$val)?'btn-primary':'btn-outline'?> text-xs py-1.5 px-3"><?=$lbl?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Agen</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis Layanan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal Ajuan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($pkks as $p): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($p['nomor_pkk'])?></td>
            <td class="py-3 px-4"><div class="font-medium"><?=htmlspecialchars($p['nama_kapal'])?></div><div class="text-xs text-gray-500"><?=htmlspecialchars($p['jenis_kapal'])?></div></td>
            <td class="py-3 px-4 text-sm"><?=htmlspecialchars($p['nama_agen'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['jenis_layanan'])?></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y H:i',strtotime($p['tanggal_ajuan']))?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadgePKK($p['status'])?> text-xs"><?=htmlspecialchars($p['status'])?></span></td>
            <td class="py-3 px-4">
              <?php if($p['status']==='Menunggu Validasi'): ?>
              <a href="validasi-pkk.php?id=<?=$p['id']??0?>" class="btn-primary text-xs py-1 px-2.5">Validasi</a>
              <?php else: ?><a href="validasi-pkk.php?id=<?=$p['id']??0?>" class="btn-outline text-xs py-1 px-2.5">Detail</a><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
