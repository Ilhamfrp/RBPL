<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Dokumen Resmi';
try {
    $docs = fetchAll("SELECT dr.*, p.nomor_pkk, k.nama_kapal FROM dokumen_resmi dr JOIN pkk p ON dr.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE p.agen_id=? ORDER BY dr.tanggal_terbit DESC", [$user['id']]);
} catch(Exception $e){ $docs=[]; }
if(empty($docs)) $docs=[
    ['nomor_dokumen'=>'PKK-RESMI-001','jenis_dokumen'=>'PKK Resmi','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','tanggal_terbit'=>'2024-11-15 10:00:00'],
    ['nomor_dokumen'=>'SPK-001-XI-2024','jenis_dokumen'=>'SPK','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','tanggal_terbit'=>'2024-11-15 14:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Dokumen Resmi</h1>
    <p class="text-gray-500 text-sm">Dokumen yang telah diterbitkan oleh KSOP</p></div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. Dokumen</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal Terbit</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($docs as $d): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium"><?=htmlspecialchars($d['nomor_dokumen'])?></td>
            <td class="py-3 px-4"><span class="badge bg-indigo-100 text-indigo-800 text-xs"><?=htmlspecialchars($d['jenis_dokumen'])?></span></td>
            <td class="py-3 px-4"><?=htmlspecialchars($d['nomor_pkk'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($d['nama_kapal'])?></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y H:i',strtotime($d['tanggal_terbit']))?></td>
            <td class="py-3 px-4">
              <?php if($d['path_dokumen']??''): ?>
              <a href="<?=htmlspecialchars($d['path_dokumen'])?>" class="btn-outline text-xs py-1 px-2.5" download>Unduh</a>
              <?php else: ?><span class="text-xs text-gray-400">Belum tersedia</span><?php endif; ?>
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
