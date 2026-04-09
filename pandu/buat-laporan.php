<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Pandu');
$user=getUser(); $pageTitle='Buat Laporan Pelayanan';
$spkId=intval($_GET['spk_id']??0);
$errors=[]; $success=false;

try {
    $spkList=fetchAll("SELECT s.*,p.nomor_pkk,k.nama_kapal FROM spk s JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE s.pandu_id=? AND s.status='Terbit'",[$user['id']]);
} catch(Exception $e){ $spkList=[]; }
if(empty($spkList)) $spkList=[['id'=>2,'nomor_spk'=>'SPK/002/XI/2024','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon']];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $d=$_POST;
    if(!($d['spk_id']??'')) $errors[]='SPK harus dipilih';
    if(!($d['tgl_mulai']??'')) $errors[]='Waktu mulai realisasi harus diisi';
    if(!($d['tgl_selesai']??'')) $errors[]='Waktu selesai realisasi harus diisi';
    if(!($d['catatan']??'')) $errors[]='Catatan pelayanan harus diisi';
    if(empty($errors)){
        try {
            $noLap='LP-'.date('Y').'-'.str_pad(rand(100,999),3,'0',STR_PAD_LEFT);
            $lampPath='';
            if(isset($_FILES['lampiran']) && $_FILES['lampiran']['error']===UPLOAD_ERR_OK){
                $ext=pathinfo($_FILES['lampiran']['name'],PATHINFO_EXTENSION);
                $fn=uniqid('lap_').'.'.$ext;
                move_uploaded_file($_FILES['lampiran']['tmp_name'],__DIR__.'/../uploads/dokumen/'.$fn);
                $lampPath='/uploads/dokumen/'.$fn;
            }
            query("INSERT INTO laporan_pelayanan (nomor_laporan,spk_id,pandu_id,tanggal_mulai_realisasi,tanggal_selesai_realisasi,kondisi_cuaca,kecepatan_angin,tinggi_gelombang,jumlah_kapal_tunda,catatan_pelayanan,kendala,status_laporan,path_lampiran) VALUES (?,?,?,?,?,?,?,?,?,?,?,'Dikirim',?)",
                [$noLap,$d['spk_id'],$user['id'],$d['tgl_mulai'],$d['tgl_selesai'],$d['cuaca']??'Cerah',$d['angin']??'',$d['gelombang']??'',$d['kapal_tunda']??0,$d['catatan'],$d['kendala']??'',$lampPath]);
            query("UPDATE spk SET status='Selesai' WHERE id=?",[$d['spk_id']]);
            // Notify KSOP
            $ksops=fetchAll("SELECT u.id FROM users u JOIN roles r ON u.role_id=r.id WHERE r.nama_role='KSOP'");
            foreach($ksops as $k) query("INSERT INTO notifikasi (user_id,judul,pesan,jenis) VALUES (?,?,?,'Info')",[$k['id'],'Laporan Pelayanan Baru','Laporan '.$noLap.' dari '.htmlspecialchars($user['nama']).' siap ditinjau']);
            $success=true;
        } catch(Exception $e){ $errors[]='Gagal: '.$e->getMessage(); }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('spk-jadwal.php','SPK &amp; Jadwal'); ?>
    <h1 class="text-2xl font-bold mb-1">Buat Laporan Pelayanan</h1>
    <p class="text-gray-500 text-sm">Dokumentasi realisasi pelayanan kapal</p></div>
  <?php if($success): ?><div class="alert-success mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">Laporan berhasil dikirim! Tim KSOP akan meninjau laporan Anda.</span></div><?php endif; ?>
  <?php if(!empty($errors)): ?><div class="alert-error mb-6"><ul class="text-sm list-disc list-inside"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="space-y-6">
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Data SPK</h3>
      <div><label class="form-label">SPK <span class="text-red-500">*</span></label>
        <select name="spk_id" class="form-input" required>
          <option value="">Pilih SPK</option>
          <?php foreach($spkList as $s): ?><option value="<?=$s['id']?>" <?=($spkId==$s['id'])?'selected':''?>><?=htmlspecialchars($s['nomor_spk'])?> - <?=htmlspecialchars($s['nama_kapal'])?></option><?php endforeach; ?>
        </select></div>
    </div>
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Waktu Realisasi</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Mulai Realisasi <span class="text-red-500">*</span></label><input type="datetime-local" name="tgl_mulai" class="form-input" required></div>
        <div><label class="form-label">Selesai Realisasi <span class="text-red-500">*</span></label><input type="datetime-local" name="tgl_selesai" class="form-input" required></div>
      </div>
    </div>
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Kondisi Pelayanan</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Kondisi Cuaca</label>
          <select name="cuaca" class="form-input">
            <?php foreach(['Cerah','Berawan','Hujan','Badai','Berkabut'] as $c): ?><option value="<?=$c?>"><?=$c?></option><?php endforeach; ?>
          </select></div>
        <div><label class="form-label">Jumlah Kapal Tunda</label><input type="number" name="kapal_tunda" class="form-input" min="0" value="0"></div>
        <div><label class="form-label">Kecepatan Angin</label><input type="text" name="angin" class="form-input" placeholder="misal: 15 knot"></div>
        <div><label class="form-label">Tinggi Gelombang</label><input type="text" name="gelombang" class="form-input" placeholder="misal: 0.5 m"></div>
      </div>
    </div>
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Catatan &amp; Laporan</h3>
      <div class="space-y-4">
        <div><label class="form-label">Catatan Pelayanan <span class="text-red-500">*</span></label><textarea name="catatan" rows="4" class="form-input" placeholder="Deskripsi lengkap pelaksanaan pelayanan..." required></textarea></div>
        <div><label class="form-label">Kendala (jika ada)</label><textarea name="kendala" rows="2" class="form-input" placeholder="Kendala yang dihadapi selama pelayanan..."></textarea></div>
        <div><label class="form-label">Lampiran</label><input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700"></div>
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="btn-primary px-8">Kirim Laporan</button>
      <a href="spk-jadwal.php" class="btn-outline px-6">Batal</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
