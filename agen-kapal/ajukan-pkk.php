<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Ajukan PKK Baru';
$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if (!($d['nama_kapal']??'')) $errors[]='Nama kapal harus diisi';
    if (!($d['jenis_kapal']??'')) $errors[]='Jenis kapal harus dipilih';
    if (!($d['jenis_layanan']??'')) $errors[]='Jenis layanan harus dipilih';
    if (!($d['tanggal_kedatangan']??'')) $errors[]='Tanggal kedatangan harus diisi';
    if (!($d['jam_kedatangan']??'')) $errors[]='Jam kedatangan harus diisi';
    if (!($d['dermaga']??'')) $errors[]='Dermaga harus dipilih';
    if (empty($_FILES['dokumen']['name'][0])) $errors[]='Dokumen pendukung harus diunggah';

    if (empty($errors)) {
        try {
            // Insert or get kapal
            $kapal = fetchOne("SELECT id FROM kapal WHERE nama_kapal=? AND jenis_kapal=?", [$d['nama_kapal'], $d['jenis_kapal']]);
            if (!$kapal) {
                query("INSERT INTO kapal (nama_kapal,jenis_kapal,bendera,gt) VALUES (?,?,?,?)", [$d['nama_kapal'],$d['jenis_kapal'],$d['bendera']??'',$d['gt']??0]);
                $kapalId = getDB()->lastInsertId();
            } else { $kapalId = $kapal['id']; }

            $noPKK = 'PKK-' . date('Y') . '-' . str_pad(rand(100,999), 3, '0', STR_PAD_LEFT);
            query("INSERT INTO pkk (nomor_pkk,agen_id,kapal_id,jenis_layanan,tanggal_kedatangan,jam_kedatangan,tanggal_keberangkatan,jam_keberangkatan,dermaga_diminta,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?)",
                [$noPKK,$user['id'],$kapalId,$d['jenis_layanan'],$d['tanggal_kedatangan'],$d['jam_kedatangan'],$d['tanggal_keberangkatan']??null,$d['jam_keberangkatan']??null,$d['dermaga'],$d['keterangan']??'']);
            $pkkId = getDB()->lastInsertId();

            // Handle file uploads
            foreach ($_FILES['dokumen']['name'] as $i => $fname) {
                if ($fname && $_FILES['dokumen']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($fname, PATHINFO_EXTENSION);
                    $newName = uniqid('dok_') . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/dokumen/' . $newName;
                    move_uploaded_file($_FILES['dokumen']['tmp_name'][$i], $dest);
                    $size = round($_FILES['dokumen']['size'][$i] / 1024) . ' KB';
                    query("INSERT INTO dokumen_pkk (pkk_id,nama_file,path_file,ukuran_file) VALUES (?,?,?,?)", [$pkkId, $fname, '/uploads/dokumen/'.$newName, $size]);
                }
            }
            // Notif to KSOP
            $ksops = fetchAll("SELECT u.id FROM users u JOIN roles r ON u.role_id=r.id WHERE r.nama_role='KSOP'");
            foreach ($ksops as $k) {
                query("INSERT INTO notifikasi (user_id,judul,pesan,jenis) VALUES (?,?,?,?)", [$k['id'],'PKK Baru Masuk','PKK '.$noPKK.' dari '.htmlspecialchars($user['nama']).' menunggu validasi','Info']);
            }
            $success = true;
        } catch (Exception $e) { $errors[] = 'Gagal menyimpan: ' . $e->getMessage(); }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Ajukan PKK Baru</h1>
    <p class="text-gray-500 text-sm">Isi formulir permohonan kunjungan kapal</p>
  </div>

  <?php if ($success): ?>
  <div class="alert-success mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">PKK berhasil diajukan! Tim KSOP akan memvalidasi permohonan Anda.</span></div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
  <div class="alert-error mb-6">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <ul class="text-sm list-disc list-inside"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
  </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="space-y-6">
    <!-- Data Kapal -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
        Data Kapal
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Nama Kapal <span class="text-red-500">*</span></label><input type="text" name="nama_kapal" class="form-input" placeholder="MV Ocean Star" value="<?=htmlspecialchars($_POST['nama_kapal']??'')?>"></div>
        <div><label class="form-label">Jenis Kapal <span class="text-red-500">*</span></label>
          <select name="jenis_kapal" class="form-input"><option value="">Pilih jenis kapal</option>
          <?php foreach(['Kontainer','Tanker','Bulk Carrier','General Cargo','RoRo','Penumpang','Lainnya'] as $j): ?>
          <option value="<?=$j?>" <?=(($_POST['jenis_kapal']??'')===$j)?'selected':''?>><?=$j?></option>
          <?php endforeach; ?></select></div>
        <div><label class="form-label">Bendera</label><input type="text" name="bendera" class="form-input" placeholder="Indonesia" value="<?=htmlspecialchars($_POST['bendera']??'')?>"></div>
        <div><label class="form-label">Gross Tonnage (GT)</label><input type="number" name="gt" class="form-input" placeholder="50000" value="<?=htmlspecialchars($_POST['gt']??'')?>"></div>
      </div>
    </div>

    <!-- Jenis Layanan -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Jenis Layanan &amp; Jadwal
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Jenis Layanan <span class="text-red-500">*</span></label>
          <select name="jenis_layanan" class="form-input"><option value="">Pilih jenis layanan</option>
          <?php foreach(['Kedatangan','Keberangkatan','Pindah Tambat'] as $j): ?><option value="<?=$j?>" <?=(($_POST['jenis_layanan']??'')===$j)?'selected':''?>><?=$j?></option><?php endforeach; ?></select></div>
        <div><label class="form-label">Dermaga <span class="text-red-500">*</span></label>
          <select name="dermaga" class="form-input"><option value="">Pilih dermaga</option>
          <?php foreach(['D-01','D-02','D-03','D-04','D-05'] as $d): ?><option value="<?=$d?>" <?=(($_POST['dermaga']??'')===$d)?'selected':''?>><?=$d?></option><?php endforeach; ?></select></div>
        <div><label class="form-label">Tanggal Kedatangan <span class="text-red-500">*</span></label><input type="date" name="tanggal_kedatangan" class="form-input" value="<?=htmlspecialchars($_POST['tanggal_kedatangan']??'')?>"></div>
        <div><label class="form-label">Jam Kedatangan <span class="text-red-500">*</span></label><input type="time" name="jam_kedatangan" class="form-input" value="<?=htmlspecialchars($_POST['jam_kedatangan']??'')?>"></div>
        <div><label class="form-label">Tanggal Keberangkatan</label><input type="date" name="tanggal_keberangkatan" class="form-input" value="<?=htmlspecialchars($_POST['tanggal_keberangkatan']??'')?>"></div>
        <div><label class="form-label">Jam Keberangkatan</label><input type="time" name="jam_keberangkatan" class="form-input" value="<?=htmlspecialchars($_POST['jam_keberangkatan']??'')?>"></div>
      </div>
    </div>

    <!-- Dokumen -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        Upload Dokumen Pendukung <span class="text-red-500">*</span>
      </h3>
      <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
        <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p class="text-sm text-gray-600 mb-1">Klik untuk upload atau drag & drop</p>
        <p class="text-xs text-gray-400">PDF, DOC, DOCX (maks. 10MB per file)</p>
        <input type="file" name="dokumen[]" multiple accept=".pdf,.doc,.docx" class="mt-3 block mx-auto text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
      </div>
      <p class="text-xs text-gray-500 mt-2">Dokumen yang diperlukan: Manifest, Crew List, Surat Permohonan, Sertifikat Kapal</p>
    </div>

    <!-- Keterangan -->
    <div class="card p-6">
      <label class="form-label">Keterangan Tambahan</label>
      <textarea name="keterangan" rows="3" class="form-input" placeholder="Informasi tambahan tentang permohonan ini..."><?=htmlspecialchars($_POST['keterangan']??'')?></textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="btn-primary px-8">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajukan PKK
      </button>
      <a href="dashboard.php" class="btn-outline px-6">Batal</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
