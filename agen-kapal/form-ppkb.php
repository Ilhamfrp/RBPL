<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Ajukan PPKB';
$errors = []; $success = false;

try {
    $pkkList = fetchAll("SELECT p.id, p.nomor_pkk, k.nama_kapal FROM pkk p JOIN kapal k ON p.kapal_id=k.id WHERE p.agen_id=? AND p.status='Divalidasi'", [$user['id']]);
} catch(Exception $e) { $pkkList = []; }
if (empty($pkkList)) $pkkList = [['id'=>1,'nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star'],['id'=>3,'nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if (!($d['pkk_id']??'')) $errors[]='PKK harus dipilih';
    if (!floatval($d['jasa_labuh']??0) && !floatval($d['jasa_tambat']??0)) $errors[]='Minimal satu jenis biaya harus diisi';
    if (empty($errors)) {
        try {
            $total = floatval($d['jasa_labuh']??0)+floatval($d['jasa_tambat']??0)+floatval($d['jasa_pemanduan']??0)+floatval($d['jasa_penundaan']??0)+floatval($d['biaya_lain']??0);
            $noPPKB = 'PPKB-'.date('Y').'-'.str_pad(rand(100,999),3,'0',STR_PAD_LEFT);
            query("INSERT INTO ppkb (nomor_ppkb,pkk_id,agen_id,jasa_labuh,jasa_tambat,jasa_pemanduan,jasa_penundaan,biaya_lain,total_biaya,catatan,status_ppkb) VALUES (?,?,?,?,?,?,?,?,?,?,'Diajukan')",
                [$noPPKB,$d['pkk_id'],$user['id'],$d['jasa_labuh']??0,$d['jasa_tambat']??0,$d['jasa_pemanduan']??0,$d['jasa_penundaan']??0,$d['biaya_lain']??0,$total,$d['catatan']??'']);
            $success = true;
        } catch(Exception $e) { $errors[]='Gagal menyimpan: '.$e->getMessage(); }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Ajukan PPKB</h1>
    <p class="text-gray-500 text-sm">Permohonan Perincian Kebutuhan Biaya</p>
  </div>
  <?php if($success): ?><div class="alert-success mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">PPKB berhasil diajukan! Silakan lakukan pembayaran EPB.</span></div><?php endif; ?>
  <?php if(!empty($errors)): ?><div class="alert-error mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><ul class="text-sm list-disc list-inside"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>

  <form method="post" id="ppkbForm" class="space-y-6">
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Pilih PKK</h3>
      <div><label class="form-label">Nomor PKK <span class="text-red-500">*</span></label>
        <select name="pkk_id" class="form-input" required>
          <option value="">Pilih PKK yang sudah divalidasi</option>
          <?php foreach($pkkList as $p): ?><option value="<?=$p['id']?>" <?=(($_POST['pkk_id']??'')==$p['id'])?'selected':''?>><?=htmlspecialchars($p['nomor_pkk'])?> - <?=htmlspecialchars($p['nama_kapal'])?></option><?php endforeach; ?>
        </select></div>
    </div>

    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Rincian Biaya</h3>
      <div class="space-y-4" id="biayaFields">
        <?php foreach([['jasa_labuh','Jasa Labuh'],['jasa_tambat','Jasa Tambat'],['jasa_pemanduan','Jasa Pemanduan'],['jasa_penundaan','Jasa Penundaan'],['biaya_lain','Biaya Lainnya']] as [$name,$label]): ?>
        <div class="flex items-center gap-4">
          <label class="form-label w-40 mb-0"><?=$label?></label>
          <div class="flex-1 flex items-center gap-2">
            <span class="text-gray-500 text-sm">Rp</span>
            <input type="number" name="<?=$name?>" class="form-input biaya-input" placeholder="0" min="0" value="<?=htmlspecialchars($_POST[$name]??'0')?>" oninput="hitungTotal()">
          </div>
        </div>
        <?php endforeach; ?>
        <div class="pt-4 border-t flex items-center gap-4">
          <span class="font-semibold text-gray-900 w-40">Total Biaya</span>
          <div class="flex-1">
            <p class="text-xl font-bold text-blue-700" id="totalBiaya">Rp 0</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-6">
      <label class="form-label">Catatan</label>
      <textarea name="catatan" rows="3" class="form-input" placeholder="Keterangan tambahan..."><?=htmlspecialchars($_POST['catatan']??'')?></textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="btn-primary px-8">Ajukan PPKB</button>
      <a href="dashboard.php" class="btn-outline px-6">Batal</a>
    </div>
  </form>
</div>
<script>
function hitungTotal() {
  let total = 0;
  document.querySelectorAll('.biaya-input').forEach(i => total += parseFloat(i.value)||0);
  document.getElementById('totalBiaya').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
hitungTotal();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
