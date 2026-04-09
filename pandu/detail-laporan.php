<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Pandu');
$user=getUser(); $pageTitle='Detail Laporan Pelayanan';
$id=intval($_GET['id']??0);
try {
    $lap=$id?fetchOne("SELECT lp.*,s.nomor_spk,p.nomor_pkk,k.nama_kapal,k.jenis_kapal FROM laporan_pelayanan lp JOIN spk s ON lp.spk_id=s.id JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE lp.id=? AND lp.pandu_id=?",[$id,$user['id']]):null;
    $tinjauan=$id?fetchOne("SELECT t.*,u.nama_lengkap FROM tinjauan_laporan t JOIN users u ON t.ksop_id=u.id WHERE t.laporan_id=?",[$id]):null;
} catch(Exception $e){ $lap=null;$tinjauan=null; }
if(!$lap) $lap=['id'=>1,'nomor_laporan'=>'LP-2024-001','nomor_spk'=>'SPK/001/XI/2024','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','tanggal_mulai_realisasi'=>'2024-11-16 08:00:00','tanggal_selesai_realisasi'=>'2024-11-16 10:00:00','kondisi_cuaca'=>'Cerah','kecepatan_angin'=>'12 knot','tinggi_gelombang'=>'0.3 m','jumlah_kapal_tunda'=>2,'catatan_pelayanan'=>'Pelayanan berjalan lancar. Kapal berhasil ditambat di dermaga D-01 tanpa kendala.','kendala'=>'','status_laporan'=>'Disetujui','created_at'=>'2024-11-16 10:30:00'];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('riwayat-pelayanan.php','Riwayat Pelayanan'); ?>
    <h1 class="text-2xl font-bold mb-1">Detail Laporan Pelayanan</h1>
    <p class="text-gray-500 text-sm"><?=htmlspecialchars($lap['nomor_laporan'])?></p></div>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="card p-6">
        <h3 class="font-semibold mb-4">Informasi Pelayanan</h3>
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
          <?php foreach([['No. Laporan',$lap['nomor_laporan']],['SPK',$lap['nomor_spk']??'-'],['PKK',$lap['nomor_pkk']??'-'],['Kapal',$lap['nama_kapal']],['Jenis',$lap['jenis_kapal']??'-'],['Status',$lap['status_laporan']]] as [$l,$v]): ?>
          <div><p class="text-xs text-gray-500"><?=$l?></p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($v)?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card p-6">
        <h3 class="font-semibold mb-4">Realisasi Waktu</h3>
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
          <div><p class="text-xs text-gray-500">Mulai</p><p class="text-sm font-medium mt-0.5"><?=date('d/m/Y H:i',strtotime($lap['tanggal_mulai_realisasi']))?></p></div>
          <div><p class="text-xs text-gray-500">Selesai</p><p class="text-sm font-medium mt-0.5"><?=date('d/m/Y H:i',strtotime($lap['tanggal_selesai_realisasi']??$lap['tanggal_mulai_realisasi']))?></p></div>
          <div><p class="text-xs text-gray-500">Kondisi Cuaca</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($lap['kondisi_cuaca']??'-')?></p></div>
          <div><p class="text-xs text-gray-500">Kapal Tunda</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($lap['jumlah_kapal_tunda']??'0')?> unit</p></div>
          <div><p class="text-xs text-gray-500">Kecepatan Angin</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($lap['kecepatan_angin']??'-')?></p></div>
          <div><p class="text-xs text-gray-500">Tinggi Gelombang</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($lap['tinggi_gelombang']??'-')?></p></div>
        </div>
      </div>
      <div class="card p-6">
        <h3 class="font-semibold mb-3">Catatan Pelayanan</h3>
        <p class="text-sm text-gray-800 bg-gray-50 p-4 rounded-lg leading-relaxed"><?=nl2br(htmlspecialchars($lap['catatan_pelayanan']??''))?></p>
        <?php if($lap['kendala']??''): ?>
        <h3 class="font-semibold mt-4 mb-2 text-red-700">Kendala</h3>
        <p class="text-sm text-red-800 bg-red-50 p-4 rounded-lg"><?=nl2br(htmlspecialchars($lap['kendala']))?></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="space-y-4 self-start">
      <div class="card p-6">
        <h3 class="font-semibold mb-3">Status Laporan</h3>
        <span class="badge <?=statusBadgeLaporan($lap['status_laporan'])?> text-sm px-3 py-1"><?=htmlspecialchars($lap['status_laporan'])?></span>
        <p class="text-xs text-gray-500 mt-2">Dikirim: <?=date('d/m/Y H:i',strtotime($lap['created_at']))?></p>
      </div>
      <?php if($tinjauan): ?>
      <div class="card p-6 <?=$tinjauan['status_tinjauan']==='Disetujui'?'border-green-200 bg-green-50':'border-yellow-200 bg-yellow-50'?>">
        <h3 class="font-semibold mb-2">Catatan KSOP</h3>
        <p class="text-xs font-medium"><?=htmlspecialchars($tinjauan['nama_lengkap']??'-')?></p>
        <p class="text-sm mt-2"><?=htmlspecialchars($tinjauan['catatan_tinjauan']??'')?></p>
        <p class="text-xs text-gray-500 mt-2"><?=date('d/m/Y H:i',strtotime($tinjauan['tanggal_tinjauan']))?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
