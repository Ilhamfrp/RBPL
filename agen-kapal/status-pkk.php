<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Status PKK';
$id = intval($_GET['id'] ?? 0);

try {
    $pkk = $id ? fetchOne("SELECT p.*, k.nama_kapal, k.jenis_kapal, k.bendera, k.gt FROM pkk p JOIN kapal k ON p.kapal_id=k.id WHERE p.id=? AND p.agen_id=?", [$id, $user['id']]) : null;
    $docs = $id ? fetchAll("SELECT * FROM dokumen_pkk WHERE pkk_id=?", [$id]) : [];
} catch(Exception $e) { $pkk = null; $docs = []; }

if (!$pkk) {
    $pkk = ['nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','bendera'=>'Indonesia','gt'=>45000,'jenis_layanan'=>'Kedatangan','tanggal_kedatangan'=>'2024-11-16','jam_kedatangan'=>'08:00:00','dermaga_diminta'=>'D-01','keterangan'=>'Kapal membawa muatan kontainer dari Singapura','status'=>'Dijadwalkan','tanggal_ajuan'=>'2024-11-14 09:00:00','catatan_ksop'=>'Dokumen lengkap dan valid'];
    $docs = [['nama_file'=>'Manifest.pdf','ukuran_file'=>'2.4 MB','status_verifikasi'=>'Valid'],['nama_file'=>'Crew_List.pdf','ukuran_file'=>'1.8 MB','status_verifikasi'=>'Valid']];
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <?php backBtn('riwayat-pkk.php','Kembali ke Riwayat PKK'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Status PKK</h1>
    <p class="text-gray-500 text-sm"><?=htmlspecialchars($pkk['nomor_pkk'])?></p>
  </div>

  <!-- Status Banner -->
  <div class="mb-6 p-4 rounded-xl border <?= in_array($pkk['status'],['Divalidasi','Dijadwalkan','Selesai']) ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' ?>">
    <div class="flex items-center gap-3">
      <?php if(in_array($pkk['status'],['Divalidasi','Dijadwalkan','Selesai'])): ?>
      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <?php else: ?>
      <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <?php endif; ?>
      <div>
        <p class="font-semibold text-gray-900">Status: <?=htmlspecialchars($pkk['status'])?></p>
        <?php if($pkk['catatan_ksop']??''): ?><p class="text-sm text-gray-600 mt-0.5">Catatan KSOP: <?=htmlspecialchars($pkk['catatan_ksop'])?></p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <!-- Info Kapal -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Informasi Kapal</h3>
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
          <?php foreach([['Nama Kapal',$pkk['nama_kapal']],['Jenis Kapal',$pkk['jenis_kapal']],['Bendera',$pkk['bendera']??'-'],['Gross Tonnage',$pkk['gt'].' GT'],['Jenis Layanan',$pkk['jenis_layanan']],['Dermaga',$pkk['dermaga_diminta']]] as [$l,$v]): ?>
          <div><p class="text-xs text-gray-500"><?=$l?></p><p class="text-sm font-medium text-gray-900 mt-0.5"><?=htmlspecialchars($v??'-')?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Jadwal -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Jadwal</h3>
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
          <div><p class="text-xs text-gray-500">Tanggal Kedatangan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['tanggal_kedatangan']??'-')?></p></div>
          <div><p class="text-xs text-gray-500">Jam Kedatangan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['jam_kedatangan']??'-')?></p></div>
          <?php if($pkk['tanggal_keberangkatan']??''): ?>
          <div><p class="text-xs text-gray-500">Tanggal Keberangkatan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['tanggal_keberangkatan'])?></p></div>
          <div><p class="text-xs text-gray-500">Jam Keberangkatan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['jam_keberangkatan']??'-')?></p></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Dokumen -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Dokumen</h3>
      <div class="space-y-3">
        <?php foreach($docs as $doc): ?>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div class="flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-xs font-medium text-gray-800"><?=htmlspecialchars($doc['nama_file'])?></span>
          </div>
          <p class="text-xs text-gray-500"><?=htmlspecialchars($doc['ukuran_file']??'')?></p>
          <?php if($doc['status_verifikasi']??''): ?>
          <span class="badge <?=$doc['status_verifikasi']==='Valid'?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'?> text-xs mt-1"><?=$doc['status_verifikasi']?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(empty($docs)): ?><p class="text-xs text-gray-400">Belum ada dokumen</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
