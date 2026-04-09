<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Bayar EPB';
$errors=[]; $success=false;

try {
    $ppkbList = fetchAll("SELECT pp.*, p.nomor_pkk, k.nama_kapal FROM ppkb pp JOIN pkk p ON pp.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE pp.agen_id=? AND pp.status_ppkb='Diajukan'", [$user['id']]);
} catch(Exception $e){ $ppkbList=[]; }
if(empty($ppkbList)) $ppkbList=[['id'=>1,'nomor_ppkb'=>'PPKB-2024-001','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','total_biaya'=>5000000]];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $d=$_POST;
    if(!($d['ppkb_id']??'')) $errors[]='PPKB harus dipilih';
    if(!($d['metode']??'')) $errors[]='Metode pembayaran harus dipilih';
    if(!($d['nomor_ref']??'')) $errors[]='Nomor referensi harus diisi';
    if(empty($_FILES['bukti']['name'])) $errors[]='Bukti pembayaran harus diunggah';
    if(empty($errors)){
        try {
            $noEPB='EPB-'.date('Y').'-'.str_pad(rand(100,999),3,'0',STR_PAD_LEFT);
            $ppkb = fetchOne("SELECT total_biaya FROM ppkb WHERE id=?",[$d['ppkb_id']]);
            $buktiPath=''; 
            if($_FILES['bukti']['error']===UPLOAD_ERR_OK){
                $ext=pathinfo($_FILES['bukti']['name'],PATHINFO_EXTENSION);
                $fname=uniqid('epb_').'.'.$ext;
                move_uploaded_file($_FILES['bukti']['tmp_name'],__DIR__.'/../uploads/dokumen/'.$fname);
                $buktiPath='/uploads/dokumen/'.$fname;
            }
            query("INSERT INTO pembayaran (nomor_epb,ppkb_id,agen_id,jumlah_bayar,metode_pembayaran,nomor_referensi,bukti_pembayaran,status_pembayaran,tanggal_pembayaran) VALUES (?,?,?,?,?,?,?,'Lunas',NOW())",
                [$noEPB,$d['ppkb_id'],$user['id'],$ppkb['total_biaya']??0,$d['metode'],$d['nomor_ref'],$buktiPath]);
            $success=true;
        } catch(Exception $e){ $errors[]='Gagal: '.$e->getMessage(); }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Bayar EPB</h1>
    <p class="text-gray-500 text-sm">Estimasi Penghitungan Biaya</p>
  </div>
  <?php if($success): ?><div class="alert-success mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">Pembayaran EPB berhasil! Dokumen resmi akan segera diterbitkan.</span></div><?php endif; ?>
  <?php if(!empty($errors)): ?><div class="alert-error mb-6"><ul class="text-sm list-disc list-inside"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="space-y-6">
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Pilih PPKB</h3>
      <select name="ppkb_id" class="form-input" required onchange="updateTotal(this)">
        <option value="">Pilih PPKB</option>
        <?php foreach($ppkbList as $p): ?><option value="<?=$p['id']?>" data-total="<?=$p['total_biaya']?>"><?=htmlspecialchars($p['nomor_ppkb'])?> - <?=htmlspecialchars($p['nama_kapal'])?></option><?php endforeach; ?>
      </select>
      <div id="totalPanel" class="hidden mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
        <p class="text-sm text-gray-600">Total Tagihan</p>
        <p class="text-2xl font-bold text-blue-700 mt-1" id="totalAmt">Rp 0</p>
      </div>
    </div>

    <div class="card p-6">
      <h3 class="font-semibold mb-4">Informasi Pembayaran</h3>
      <div class="space-y-4">
        <div><label class="form-label">Metode Pembayaran <span class="text-red-500">*</span></label>
          <select name="metode" class="form-input" required>
            <option value="">Pilih metode</option>
            <?php foreach(['Transfer Bank','Virtual Account','Tunai'] as $m): ?><option value="<?=$m?>"><?=$m?></option><?php endforeach; ?>
          </select></div>
        <div><label class="form-label">Bank Pengirim</label><input type="text" name="bank" class="form-input" placeholder="BCA, Mandiri, BNI..."></div>
        <div><label class="form-label">Nomor Referensi Transaksi <span class="text-red-500">*</span></label><input type="text" name="nomor_ref" class="form-input" placeholder="Nomor transfer/transaksi"></div>
        <div>
          <label class="form-label">Bukti Pembayaran <span class="text-red-500">*</span></label>
          <input type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
        </div>
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="btn-primary px-8">Konfirmasi Pembayaran</button>
      <a href="dashboard.php" class="btn-outline px-6">Batal</a>
    </div>
  </form>
</div>
<script>
function updateTotal(sel){
  const opt=sel.options[sel.selectedIndex];
  const total=parseFloat(opt.dataset.total)||0;
  if(total>0){
    document.getElementById('totalPanel').classList.remove('hidden');
    document.getElementById('totalAmt').textContent='Rp '+total.toLocaleString('id-ID');
  } else { document.getElementById('totalPanel').classList.add('hidden'); }
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
