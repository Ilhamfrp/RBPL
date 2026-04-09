<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Planner');
$user=getUser(); $pageTitle='Alokasi Sumber Daya';
try {
    $alokasi=fetchAll("SELECT a.*,j.tanggal_mulai,p.nomor_pkk,k.nama_kapal FROM alokasi_sumber_daya a JOIN jadwal_pelayanan j ON a.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id ORDER BY j.tanggal_mulai DESC");
    $dermaga=fetchAll("SELECT * FROM dermaga");
} catch(Exception $e){ $alokasi=[];$dermaga=[]; }
if(empty($alokasi)) $alokasi=[
    ['nama_sumber_daya'=>'Capt. Faisal Ibrahim','jenis_sumber_daya'=>'Pandu','nama_kapal'=>'MV Ocean Star','nomor_pkk'=>'PKK-2024-001','tanggal_mulai'=>'2024-11-16 08:00:00','status'=>'Dialokasikan'],
    ['nama_sumber_daya'=>'Capt. Ahmad Gunawan','jenis_sumber_daya'=>'Pandu','nama_kapal'=>'MV Blue Horizon','nomor_pkk'=>'PKK-2024-003','tanggal_mulai'=>'2024-11-18 08:00:00','status'=>'Dialokasikan'],
];
if(empty($dermaga)) $dermaga=[
    ['kode_dermaga'=>'D-01','nama_dermaga'=>'Dermaga 1 Kontainer','status'=>'Tersedia'],
    ['kode_dermaga'=>'D-02','nama_dermaga'=>'Dermaga 2 Kontainer','status'=>'Tersedia'],
    ['kode_dermaga'=>'D-03','nama_dermaga'=>'Dermaga 3 General Cargo','status'=>'Tersedia'],
    ['kode_dermaga'=>'D-04','nama_dermaga'=>'Dermaga 4 Tanker','status'=>'Digunakan'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6"><?php backBtn('dashboard.php','Dashboard Planner'); ?>
    <h1 class="text-2xl font-bold mb-1">Alokasi Sumber Daya</h1>
    <p class="text-gray-500 text-sm">Monitoring penggunaan dermaga dan penugasan pandu</p></div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Status Dermaga</h3>
      <div class="space-y-3">
        <?php foreach($dermaga as $d): ?>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <div>
            <p class="text-sm font-medium"><?=htmlspecialchars($d['kode_dermaga'])?></p>
            <p class="text-xs text-gray-500"><?=htmlspecialchars($d['nama_dermaga']??'')?></p>
          </div>
          <span class="badge <?=$d['status']==='Tersedia'?'bg-green-100 text-green-800':($d['status']==='Digunakan'?'bg-yellow-100 text-yellow-800':'bg-red-100 text-red-800')?> text-xs"><?=htmlspecialchars($d['status'])?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Alokasi Pandu Aktif</h3>
      <div class="space-y-3">
        <?php foreach($alokasi as $a): ?>
        <div class="p-3 bg-gray-50 rounded-lg border">
          <div class="flex items-center justify-between mb-1">
            <p class="text-sm font-medium"><?=htmlspecialchars($a['nama_sumber_daya'])?></p>
            <span class="badge bg-blue-100 text-blue-800 text-xs"><?=htmlspecialchars($a['jenis_sumber_daya'])?></span>
          </div>
          <p class="text-xs text-gray-500"><?=htmlspecialchars($a['nomor_pkk'])?> - <?=htmlspecialchars($a['nama_kapal'])?></p>
          <p class="text-xs text-gray-400"><?=date('d/m/Y H:i',strtotime($a['tanggal_mulai']))?></p>
        </div>
        <?php endforeach; ?>
        <?php if(empty($alokasi)): ?><p class="text-sm text-gray-400">Belum ada alokasi aktif</p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold mb-4">Semua Alokasi</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">Sumber Daya</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Waktu</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
        </tr></thead>
        <tbody>
          <?php foreach($alokasi as $a): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium"><?=htmlspecialchars($a['nama_sumber_daya'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($a['jenis_sumber_daya'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($a['nomor_pkk'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($a['nama_kapal'])?></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y H:i',strtotime($a['tanggal_mulai']))?></td>
            <td class="py-3 px-4"><span class="badge bg-blue-100 text-blue-800 text-xs"><?=htmlspecialchars($a['status'])?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
