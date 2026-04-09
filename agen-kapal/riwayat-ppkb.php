<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Agen Kapal');
$user = getUser();
$pageTitle = 'Riwayat PPKB';

try {
    $ppkbs = fetchAll("SELECT pp.*, p.nomor_pkk, k.nama_kapal, pm.status_pembayaran, pm.nomor_epb FROM ppkb pp JOIN pkk p ON pp.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id LEFT JOIN pembayaran pm ON pm.ppkb_id=pp.id WHERE pp.agen_id=? ORDER BY pp.tanggal_ajuan DESC", [$user['id']]);
} catch(Exception $e){ $ppkbs=[]; }
if(empty($ppkbs)) $ppkbs=[
    ['nomor_ppkb'=>'PPKB-2024-001','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','total_biaya'=>5000000,'status_ppkb'=>'Diajukan','nomor_epb'=>null,'status_pembayaran'=>null,'tanggal_ajuan'=>'2024-11-15 10:00:00'],
    ['nomor_ppkb'=>'PPKB-2024-002','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','total_biaya'=>7500000,'status_ppkb'=>'Disetujui','nomor_epb'=>'EPB-2024-001','status_pembayaran'=>'Lunas','tanggal_ajuan'=>'2024-11-12 09:00:00'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <?php backBtn('dashboard.php','Kembali ke Dashboard'); ?>
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Riwayat PPKB &amp; Pembayaran EPB</h1>
    </div>
    <a href="form-ppkb.php" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Ajukan PPKB</a>
  </div>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. PPKB</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">PKK</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Total Biaya</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status PPKB</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. EPB</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Pembayaran</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
        </tr></thead>
        <tbody>
          <?php foreach($ppkbs as $p): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($p['nomor_ppkb'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['nomor_pkk'])?></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['nama_kapal'])?></td>
            <td class="py-3 px-4 font-medium"><?=formatRupiah($p['total_biaya'])?></td>
            <td class="py-3 px-4"><span class="badge bg-blue-100 text-blue-800 text-xs"><?=htmlspecialchars($p['status_ppkb'])?></span></td>
            <td class="py-3 px-4"><?=htmlspecialchars($p['nomor_epb']??'-')?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadgePembayaran($p['status_pembayaran']??'Menunggu')?> text-xs"><?=htmlspecialchars($p['status_pembayaran']??'Belum Bayar')?></span></td>
            <td class="py-3 px-4 text-gray-600"><?=date('d/m/Y',strtotime($p['tanggal_ajuan']))?></td>
            <td class="py-3 px-4">
              <?php if(!$p['status_pembayaran'] || $p['status_pembayaran']==='Menunggu'): ?>
              <a href="bayar-epb.php" class="btn-primary text-xs py-1 px-2.5">Bayar</a>
              <?php else: ?><span class="text-green-600 text-xs font-medium">Lunas</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
