<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user = getUser();
$pageTitle = 'Validasi PKK';
$id = intval($_GET['id'] ?? 0);
$success = false; $error = '';

try {
    $pkk = $id ? fetchOne("SELECT p.*, k.nama_kapal, k.jenis_kapal, k.bendera, k.gt, u.nama_lengkap as nama_agen FROM pkk p JOIN kapal k ON p.kapal_id=k.id JOIN users u ON p.agen_id=u.id WHERE p.id=?", [$id]) : null;
    $docs = $id ? fetchAll("SELECT * FROM dokumen_pkk WHERE pkk_id=?", [$id]) : [];
} catch(Exception $e){ $pkk=null; $docs=[]; }

if (!$pkk) {
    $pkk=['id'=>1,'nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_kapal'=>'Kontainer','bendera'=>'Indonesia','gt'=>45000,'nama_agen'=>'Ahmad Rizki - PT Maritim Express','jenis_layanan'=>'Kedatangan','tanggal_kedatangan'=>'2024-11-16','jam_kedatangan'=>'08:00','tanggal_keberangkatan'=>'2024-11-18','jam_keberangkatan'=>'14:00','dermaga_diminta'=>'D-01','keterangan'=>'Kapal membawa muatan kontainer dari Singapura','status'=>'Menunggu Validasi','tanggal_ajuan'=>'2024-11-14 09:00:00'];
    $docs=[['id'=>1,'nama_file'=>'Manifest.pdf','ukuran_file'=>'2.4 MB','jenis_dokumen'=>'Manifest','status_verifikasi'=>'Belum Diperiksa'],['id'=>2,'nama_file'=>'Crew_List.pdf','ukuran_file'=>'1.8 MB','jenis_dokumen'=>'Crew List','status_verifikasi'=>'Belum Diperiksa']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');
    if (in_array($aksi, ['valid','tidak-valid'])) {
        try {
            $newStatus = ($aksi==='valid') ? 'Divalidasi' : 'Ditolak';
            query("UPDATE pkk SET status=?, catatan_ksop=? WHERE id=?", [$newStatus, $catatan, $id]);
            query("INSERT INTO validasi_pkk (pkk_id,ksop_id,status_validasi,catatan) VALUES (?,?,?,?)", [$id,$user['id'],($aksi==='valid'?'Valid':'Tidak Valid'),$catatan]);
            // Notify agen
            if($pkk['agen_id']??false) query("INSERT INTO notifikasi (user_id,judul,pesan,jenis) VALUES (?,?,?,?)", [$pkk['agen_id'],'PKK '.$newStatus,'PKK '.$pkk['nomor_pkk'].' telah '.$newStatus.' oleh KSOP. '.$catatan,($aksi==='valid'?'Sukses':'Peringatan')]);
            $success = true;
            header("Location: daftar-pkk.php?validated=1");
            exit;
        } catch(Exception $e){ $error='Gagal: '.$e->getMessage(); }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('daftar-pkk.php','Kembali ke Daftar PKK'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Validasi PKK</h1>
    <p class="text-gray-500 text-sm">Periksa kelengkapan dan keabsahan permohonan</p></div>

  <?php if($error): ?><div class="alert-error mb-6"><span class="text-sm"><?=htmlspecialchars($error)?></span></div><?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="card p-6">
        <h3 class="font-semibold mb-4">Detail Permohonan PKK</h3>
        <p class="text-sm text-gray-500 mb-4"><?=htmlspecialchars($pkk['nomor_pkk'])?></p>
        <div class="mb-6">
          <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>Informasi Kapal</h4>
          <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded-lg">
            <?php foreach([['Nama Kapal',$pkk['nama_kapal']],['Jenis Kapal',$pkk['jenis_kapal']],['Bendera',$pkk['bendera']??'-'],['GT',$pkk['gt'].' GT'],['Agen Kapal',$pkk['nama_agen']],['Jenis Layanan',$pkk['jenis_layanan']]] as [$l,$v]): ?>
            <div><p class="text-xs text-gray-500"><?=$l?></p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($v??'-')?></p></div>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2"><svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Jadwal</h4>
          <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded-lg">
            <div><p class="text-xs text-gray-500">Tgl Kedatangan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['tanggal_kedatangan']??'-')?></p></div>
            <div><p class="text-xs text-gray-500">Jam Kedatangan</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['jam_kedatangan']??'-')?></p></div>
            <div><p class="text-xs text-gray-500">Dermaga</p><p class="text-sm font-medium mt-0.5"><?=htmlspecialchars($pkk['dermaga_diminta']??'-')?></p></div>
          </div>
        </div>
      </div>

      <!-- Formulir Validasi -->
      <?php if($pkk['status']==='Menunggu Validasi'): ?>
      <div class="card p-6">
        <h3 class="font-semibold mb-4">Keputusan Validasi</h3>
        <form method="post" class="space-y-4">
          <div>
            <label class="form-label">Catatan KSOP</label>
            <textarea name="catatan" rows="3" class="form-input" placeholder="Catatan validasi atau alasan penolakan..."></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" name="aksi" value="valid" class="btn-success flex-1 justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Valid - Setujui PKK
            </button>
            <button type="submit" name="aksi" value="tidak-valid" class="btn-danger flex-1 justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              Tidak Valid - Tolak
            </button>
          </div>
        </form>
      </div>
      <?php else: ?>
      <div class="card p-4 <?=$pkk['status']==='Divalidasi'?'bg-green-50 border-green-200':'bg-red-50 border-red-200'?>">
        <p class="font-semibold text-gray-900">Status: <?=htmlspecialchars($pkk['status'])?></p>
        <?php if($pkk['catatan_ksop']??''): ?><p class="text-sm text-gray-600 mt-1"><?=htmlspecialchars($pkk['catatan_ksop'])?></p><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Dokumen -->
    <div class="card p-6 self-start">
      <h3 class="font-semibold mb-4">Dokumen Pendukung</h3>
      <div class="space-y-3">
        <?php foreach($docs as $doc): ?>
        <div class="p-3 bg-gray-50 rounded-lg border">
          <div class="flex items-center gap-2 mb-1.5">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-xs font-medium"><?=htmlspecialchars($doc['nama_file'])?></span>
          </div>
          <p class="text-xs text-gray-500"><?=htmlspecialchars($doc['ukuran_file']??'')?></p>
          <?php if($doc['path_file']??''): ?><a href="<?=htmlspecialchars($doc['path_file'])?>" class="btn-outline text-xs py-0.5 px-2 mt-2 inline-flex" download>Unduh</a><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(empty($docs)): ?><p class="text-xs text-gray-400">Belum ada dokumen</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
