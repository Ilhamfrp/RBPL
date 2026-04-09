<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('KSOP');
$user=getUser(); $pageTitle='Tinjauan Laporan';

try {
    $laporan=fetchAll("SELECT lp.*, s.nomor_spk, p.nomor_pkk, k.nama_kapal, u.nama_lengkap as nama_pandu FROM laporan_pelayanan lp JOIN spk s ON lp.spk_id=s.id JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id JOIN users u ON lp.pandu_id=u.id WHERE lp.status_laporan='Dikirim' ORDER BY lp.created_at DESC");
} catch(Exception $e){ $laporan=[]; }
if(empty($laporan)) $laporan=[
    ['id'=>1,'nomor_laporan'=>'LP-2024-001','nomor_spk'=>'SPK/001/XI/2024','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','nama_pandu'=>'Capt. Faisal Ibrahim','status_laporan'=>'Dikirim','created_at'=>'2024-11-16 18:00:00'],
    ['id'=>2,'nomor_laporan'=>'LP-2024-002','nomor_spk'=>'SPK/002/XI/2024','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','nama_pandu'=>'Capt. Ahmad Gunawan','status_laporan'=>'Dikirim','created_at'=>'2024-11-15 17:00:00'],
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $lId=intval($_POST['laporan_id']??0); $aksi=$_POST['aksi']??''; $catatan=$_POST['catatan']??'';
    if($lId && in_array($aksi,['setuju','revisi'])){
        try {
            $ns=$aksi==='setuju'?'Disetujui':'Perlu Revisi';
            query("UPDATE laporan_pelayanan SET status_laporan=? WHERE id=?",[$ns,$lId]);
            query("INSERT INTO tinjauan_laporan (laporan_id,ksop_id,status_tinjauan,catatan) VALUES (?,?,?,?)",[$lId,$user['id'],($aksi==='setuju'?'Disetujui':'Perlu Revisi'),$catatan]);
            header("Location: peninjauan-laporan.php?done=1"); exit;
        } catch(Exception $e){}
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Dashboard KSOP'); ?>
    <h1 class="text-2xl font-bold mb-1">Tinjauan Laporan Pelayanan</h1></div>
  <?php if($_GET['done']??false): ?><div class="alert-success mb-6"><span class="text-sm font-medium">Laporan berhasil ditinjau.</span></div><?php endif; ?>
  <div class="space-y-4">
    <?php foreach($laporan as $l): ?>
    <div class="card p-6" x-data="{open:false}">
      <div class="flex items-start justify-between mb-3">
        <div>
          <p class="font-semibold text-gray-900"><?=htmlspecialchars($l['nomor_laporan'])?></p>
          <p class="text-sm text-gray-500 mt-0.5"><?=htmlspecialchars($l['nomor_spk'])?> · <?=htmlspecialchars($l['nama_kapal'])?></p>
          <p class="text-xs text-gray-400 mt-0.5">Pandu: <?=htmlspecialchars($l['nama_pandu'])?> · <?=date('d/m/Y H:i',strtotime($l['created_at']))?></p>
        </div>
        <span class="badge <?=statusBadgeLaporan($l['status_laporan'])?> text-xs"><?=htmlspecialchars($l['status_laporan'])?></span>
      </div>
      <?php if($l['status_laporan']==='Dikirim'): ?>
      <div class="border-t pt-4 mt-3">
        <form method="post" class="flex flex-col sm:flex-row gap-3 items-end">
          <input type="hidden" name="laporan_id" value="<?=$l['id']?>">
          <div class="flex-1">
            <label class="form-label text-xs">Catatan Tinjauan</label>
            <input type="text" name="catatan" class="form-input text-sm" placeholder="Opsional...">
          </div>
          <button type="submit" name="aksi" value="setuju" class="btn-success text-xs py-2 px-4">Setujui</button>
          <button type="submit" name="aksi" value="revisi" class="btn-danger text-xs py-2 px-4">Minta Revisi</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if(empty($laporan)): ?><div class="text-center py-12 text-gray-500">Tidak ada laporan yang menunggu tinjauan</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
