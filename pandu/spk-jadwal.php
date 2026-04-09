<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Pandu');
$user=getUser(); $pageTitle='SPK & Jadwal';
try {
    $spks=fetchAll("SELECT s.*,j.tanggal_mulai,j.tanggal_selesai,j.catatan as catatan_jadwal,p.nomor_pkk,k.nama_kapal,k.jenis_kapal,d.kode_dermaga FROM spk s JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id LEFT JOIN dermaga d ON j.dermaga_id=d.id WHERE s.pandu_id=? ORDER BY j.tanggal_mulai DESC",[$user['id']]);
} catch(Exception $e){ $spks=[]; }
if(empty($spks)) $spks=[
    ['id'=>1,'nomor_spk'=>'SPK/001/XI/2024','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','kode_dermaga'=>'D-01','tanggal_mulai'=>'2024-11-16 08:00:00','tanggal_selesai'=>'2024-11-16 10:00:00','status'=>'Selesai','isi_instruksi'=>'Pandu kapal dari luar ke dermaga D-01. Gunakan kapal tunda 2 unit.','catatan_jadwal'=>''],
    ['id'=>2,'nomor_spk'=>'SPK/002/XI/2024','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_kapal'=>'Kontainer','kode_dermaga'=>'D-02','tanggal_mulai'=>'2024-11-18 08:00:00','tanggal_selesai'=>'2024-11-18 10:30:00','status'=>'Terbit','isi_instruksi'=>'Pandu kapal dari luar ke dermaga D-02. Kondisi cuaca cerah.','catatan_jadwal'=>''],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div><?php backBtn('dashboard.php','Dashboard Pandu'); ?><h1 class="text-2xl font-bold mb-1">Surat Perintah Kerja &amp; Jadwal</h1></div>
  </div>
  <div class="space-y-4">
    <?php foreach($spks as $s): ?>
    <div class="card p-6">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="font-bold text-lg"><?=htmlspecialchars($s['nomor_spk'])?></p>
          <div class="flex items-center gap-3 mt-1 flex-wrap">
            <span class="text-sm text-gray-600"><?=htmlspecialchars($s['nomor_pkk'])?></span>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-medium"><?=htmlspecialchars($s['nama_kapal'])?></span>
            <span class="badge bg-gray-100 text-gray-600 text-xs"><?=htmlspecialchars($s['jenis_kapal']??'')?></span>
          </div>
        </div>
        <span class="badge <?=$s['status']==='Selesai'?'bg-green-100 text-green-800':($s['status']==='Terbit'?'bg-blue-100 text-blue-800':'bg-gray-100 text-gray-600')?> text-xs"><?=htmlspecialchars($s['status'])?></span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg mb-4">
        <div><p class="text-xs text-gray-500">Dermaga</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($s['kode_dermaga']??'-')?></p></div>
        <div><p class="text-xs text-gray-500">Mulai</p><p class="text-sm font-medium mt-0.5"><?=date('d/m/Y H:i',strtotime($s['tanggal_mulai']))?></p></div>
        <div><p class="text-xs text-gray-500">Selesai</p><p class="text-sm font-medium mt-0.5"><?=date('d/m/Y H:i',strtotime($s['tanggal_selesai']??$s['tanggal_mulai']))?></p></div>
      </div>
      <div class="mb-4">
        <p class="text-xs text-gray-500 mb-1">Instruksi</p>
        <p class="text-sm text-gray-800 bg-blue-50 p-3 rounded-lg"><?=htmlspecialchars($s['isi_instruksi']??'-')?></p>
      </div>
      <?php if($s['status']==='Terbit'): ?>
      <div class="flex gap-2">
        <a href="buat-laporan.php?spk_id=<?=$s['id']?>" class="btn-primary text-xs py-1.5 px-4">Buat Laporan Realisasi</a>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
