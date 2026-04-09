<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user=getUser(); $pageTitle='Terbitkan Dokumen Resmi';
$success=false; $error='';

try {
    $pkkSiap = fetchAll("SELECT p.*, k.nama_kapal, j.id as jadwal_id FROM pkk p JOIN kapal k ON p.kapal_id=k.id LEFT JOIN jadwal_pelayanan j ON j.pkk_id=p.id WHERE p.status='Divalidasi' OR j.status='Dikonfirmasi' ORDER BY p.tanggal_ajuan DESC");
} catch(Exception $e){ $pkkSiap=[]; }
if(empty($pkkSiap)) $pkkSiap=[
    ['id'=>1,'nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_layanan'=>'Kedatangan','status'=>'Divalidasi','jadwal_id'=>1],
    ['id'=>3,'nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_layanan'=>'Kedatangan','status'=>'Dijadwalkan','jadwal_id'=>2],
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $pkkId=intval($_POST['pkk_id']??0); $jenis=$_POST['jenis_dokumen']??'';
    if($pkkId && $jenis){
        try {
            $prefix=['PKK Resmi'=>'PKK-RESMI','PPKB Resmi'=>'PPKB-RESMI','SPK'=>'SPK','Jadwal Resmi'=>'JADWAL'][$jenis]??'DOK';
            $noDoc=$prefix.'/'.str_pad(rand(1,999),3,'0',STR_PAD_LEFT).'/'.date('m/Y');
            query("INSERT INTO dokumen_resmi (nomor_dokumen,jenis_dokumen,pkk_id,ksop_id) VALUES (?,?,?,?)", [$noDoc,$jenis,$pkkId,$user['id']]);
            // Notify agen
            $pkkData=fetchOne("SELECT agen_id,nomor_pkk FROM pkk WHERE id=?",[$pkkId]);
            if($pkkData) query("INSERT INTO notifikasi (user_id,judul,pesan,jenis) VALUES (?,?,?,'Sukses')", [$pkkData['agen_id'],'Dokumen Resmi Terbit','Dokumen '.$jenis.' untuk PKK '.$pkkData['nomor_pkk'].' telah diterbitkan']);
            $success=true;
        } catch(Exception $e){ $error='Gagal: '.$e->getMessage(); }
    } else { $error='Pilih PKK dan jenis dokumen'; }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Dashboard KSOP'); ?>
    <h1 class="text-2xl font-bold mb-1">Terbitkan Dokumen Resmi</h1>
    <p class="text-gray-500 text-sm">Terbitkan dokumen resmi untuk permohonan yang sudah valid dan lunas</p></div>

  <?php if($success): ?><div class="alert-success mb-6"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">Dokumen berhasil diterbitkan!</span></div><?php endif; ?>
  <?php if($error): ?><div class="alert-error mb-6"><span class="text-sm"><?=htmlspecialchars($error)?></span></div><?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Terbitkan Dokumen Baru</h3>
      <form method="post" class="space-y-4">
        <div><label class="form-label">Pilih PKK <span class="text-red-500">*</span></label>
          <select name="pkk_id" class="form-input" required>
            <option value="">Pilih PKK</option>
            <?php foreach($pkkSiap as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['nomor_pkk'])?> - <?=htmlspecialchars($p['nama_kapal'])?></option><?php endforeach; ?>
          </select></div>
        <div><label class="form-label">Jenis Dokumen <span class="text-red-500">*</span></label>
          <select name="jenis_dokumen" class="form-input" required>
            <option value="">Pilih jenis dokumen</option>
            <?php foreach(['PKK Resmi','PPKB Resmi','SPK','Jadwal Resmi'] as $j): ?><option value="<?=$j?>"><?=$j?></option><?php endforeach; ?>
          </select></div>
        <button type="submit" class="btn-primary w-full justify-center">Terbitkan Dokumen</button>
      </form>
    </div>
    <!-- Riwayat Dokumen Terbit -->
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Dokumen Terbaru</h3>
      <?php try {
          $recentDok = fetchAll("SELECT dr.*, k.nama_kapal FROM dokumen_resmi dr JOIN pkk p ON dr.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id ORDER BY dr.tanggal_terbit DESC LIMIT 5");
      } catch(Exception $e){ $recentDok=[]; }
      if(empty($recentDok)) $recentDok=[['nomor_dokumen'=>'SPK/001/XI/2024','jenis_dokumen'=>'SPK','nama_kapal'=>'MV Ocean Star','tanggal_terbit'=>'2024-11-15 14:00:00'],['nomor_dokumen'=>'PKK-RESMI/001/11/2024','jenis_dokumen'=>'PKK Resmi','nama_kapal'=>'MV Blue Horizon','tanggal_terbit'=>'2024-11-14 10:00:00']]; ?>
      <div class="space-y-3">
        <?php foreach($recentDok as $d): ?>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
          <div>
            <p class="text-sm font-medium"><?=htmlspecialchars($d['nomor_dokumen'])?></p>
            <p class="text-xs text-gray-500"><?=htmlspecialchars($d['nama_kapal']??'').' · '.htmlspecialchars($d['jenis_dokumen'])?></p>
          </div>
          <span class="text-xs text-gray-400"><?=date('d/m/Y',strtotime($d['tanggal_terbit']))?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
