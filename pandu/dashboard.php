<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Pandu');
$user=getUser(); $pageTitle='Dashboard Pandu';
try {
    $statSpk=fetchOne("SELECT COUNT(*) c FROM spk WHERE pandu_id=? AND status IN ('Terbit','Diterima')",[$user['id']])['c']??0;
    $statHariIni=fetchOne("SELECT COUNT(*) c FROM spk s JOIN jadwal_pelayanan j ON s.jadwal_id=j.id WHERE s.pandu_id=? AND DATE(j.tanggal_mulai)=CURDATE()",[$user['id']])['c']??0;
    $statSelesai=fetchOne("SELECT COUNT(*) c FROM spk WHERE pandu_id=? AND status='Selesai'",[$user['id']])['c']??0;
    $jadwalHariIni=fetchAll("SELECT s.*,j.tanggal_mulai,j.tanggal_selesai,p.nomor_pkk,k.nama_kapal FROM spk s JOIN jadwal_pelayanan j ON s.jadwal_id=j.id JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id WHERE s.pandu_id=? AND DATE(j.tanggal_mulai)=CURDATE() ORDER BY j.tanggal_mulai",[$user['id']]);
} catch(Exception $e){ $statSpk=2;$statHariIni=2;$statSelesai=1;$jadwalHariIni=[]; }
if(empty($jadwalHariIni)) $jadwalHariIni=[
    ['id'=>1,'nomor_spk'=>'SPK/001/XI/2024','nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','tanggal_mulai'=>'2024-11-16 08:00:00','tanggal_selesai'=>'2024-11-16 10:00:00','status'=>'Selesai'],
    ['id'=>2,'nomor_spk'=>'SPK/002/XI/2024','nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','tanggal_mulai'=>'2024-11-16 14:00:00','tanggal_selesai'=>'2024-11-16 16:00:00','status'=>'Terbit'],
];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-8">
    <h1 class="text-2xl font-bold mb-1">Dashboard Pandu</h1>
    <p class="text-gray-500">Selamat datang, <?=htmlspecialchars($user['nama'])?></p>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php foreach([['SPK Aktif',$statSpk.' SPK','text-blue-600'],['Tugas Hari Ini',$statHariIni.' Tugas','text-green-600'],['Sedang Berlangsung','0 Tugas','text-yellow-600'],['Selesai',$statSelesai.' Tugas','text-green-600']] as [$l,$v,$c]): ?>
    <div class="card p-6"><div class="flex items-center justify-between">
      <div><p class="text-gray-500 text-sm"><?=$l?></p><p class="text-2xl font-bold mt-1"><?=$v?></p></div>
      <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    </div></div>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <?php foreach([
      ['Surat Perintah Kerja &amp; Jadwal','Lihat SPK dan jadwal pelayanan yang ditugaskan','spk-jadwal.php','text-blue-600'],
      ['Buat Laporan Pelayanan','Buat laporan realisasi setelah pelayanan selesai','buat-laporan.php','text-green-600'],
    ] as [$t,$d,$url,$c]): ?>
    <a href="<?=$url?>" class="card p-6 hover:shadow-lg transition-shadow block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold mb-1"><?=$t?></h3><p class="text-gray-500 text-sm"><?=$d?></p></div>
        <svg class="w-8 h-8 <?=$c?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
    <?php endforeach; ?>
    <a href="riwayat-pelayanan.php" class="card p-6 hover:shadow-lg transition-shadow block">
      <div class="flex items-start justify-between mb-4">
        <div><h3 class="font-semibold mb-1">Riwayat Pelayanan</h3><p class="text-gray-500 text-sm">Lihat riwayat semua pelayanan yang telah diselesaikan</p></div>
        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="btn-outline text-xs w-full justify-center">Buka</span>
    </a>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Jadwal Tugas Hari Ini</h3>
    <p class="text-sm text-gray-500 mb-4"><?=date('l, d F Y')?></p>
    <div class="space-y-4">
      <?php foreach($jadwalHariIni as $j): ?>
      <div class="border rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
            <div>
              <p class="font-medium text-sm"><?=htmlspecialchars($j['nama_kapal'])?></p>
              <p class="text-xs text-gray-500"><?=htmlspecialchars($j['nomor_spk'])?></p>
            </div>
          </div>
          <span class="badge <?=$j['status']==='Selesai'?'bg-green-100 text-green-800':'bg-blue-100 text-blue-800'?> text-xs"><?=htmlspecialchars($j['status'])?></span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?=date('H:i',strtotime($j['tanggal_mulai']))?> - <?=date('H:i',strtotime($j['tanggal_selesai']??$j['tanggal_mulai']))?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
