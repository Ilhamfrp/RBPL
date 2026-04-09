<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
$pageTitle = 'SIM Pelayanan Kapal - Beranda';
$filterDate = $_GET['date'] ?? '2024-11-16';

try {
    $jadwal = fetchAll("
        SELECT p.nomor_pkk, k.nama_kapal, p.jenis_layanan, 
               d.kode_dermaga, j.tanggal_mulai, j.status
        FROM jadwal_pelayanan j
        JOIN pkk p ON j.pkk_id = p.id
        JOIN kapal k ON p.kapal_id = k.id
        LEFT JOIN dermaga d ON j.dermaga_id = d.id
        WHERE DATE(j.tanggal_mulai) = ?
        ORDER BY j.tanggal_mulai ASC
    ", [$filterDate]);
} catch (Exception $e) { $jadwal = []; }

// Sample fallback data
if (empty($jadwal)) {
    $allSample = [
        '2024-11-16' => [
            ['nomor_pkk'=>'PKK-2024-001','nama_kapal'=>'MV Ocean Star','jenis_layanan'=>'Kedatangan','kode_dermaga'=>'D-01','tanggal_mulai'=>'2024-11-16 08:00:00','status'=>'Dijadwalkan'],
            ['nomor_pkk'=>'PKK-2024-002','nama_kapal'=>'MV Pacific Dream','jenis_layanan'=>'Keberangkatan','kode_dermaga'=>'D-03','tanggal_mulai'=>'2024-11-16 10:30:00','status'=>'Berlangsung'],
            ['nomor_pkk'=>'PKK-2024-003','nama_kapal'=>'MV Blue Horizon','jenis_layanan'=>'Kedatangan','kode_dermaga'=>'D-02','tanggal_mulai'=>'2024-11-16 14:00:00','status'=>'Dijadwalkan'],
            ['nomor_pkk'=>'PKK-2024-004','nama_kapal'=>'MV Sea Voyager','jenis_layanan'=>'Keberangkatan','kode_dermaga'=>'D-04','tanggal_mulai'=>'2024-11-16 16:00:00','status'=>'Dikonfirmasi'],
        ],
        '2024-11-17' => [
            ['nomor_pkk'=>'PKK-2024-005','nama_kapal'=>'MV Maritime Express','jenis_layanan'=>'Kedatangan','kode_dermaga'=>'D-01','tanggal_mulai'=>'2024-11-17 07:00:00','status'=>'Dijadwalkan'],
            ['nomor_pkk'=>'PKK-2024-006','nama_kapal'=>'MV Cargo Master','jenis_layanan'=>'Kedatangan','kode_dermaga'=>'D-02','tanggal_mulai'=>'2024-11-17 09:30:00','status'=>'Dijadwalkan'],
        ],
        '2024-11-18' => [
            ['nomor_pkk'=>'PKK-2024-007','nama_kapal'=>'MV Star Bright','jenis_layanan'=>'Kedatangan','kode_dermaga'=>'D-03','tanggal_mulai'=>'2024-11-18 08:00:00','status'=>'Dijadwalkan'],
        ],
    ];
    $jadwal = $allSample[$filterDate] ?? [];
}

function statusBadge($s) {
    $m=['Dijadwalkan'=>'bg-blue-100 text-blue-800','Berlangsung'=>'bg-green-100 text-green-800','Dikonfirmasi'=>'bg-purple-100 text-purple-800','Selesai'=>'bg-gray-100 text-gray-700','Ditunda'=>'bg-yellow-100 text-yellow-800','Draft'=>'bg-gray-100 text-gray-600'];
    return $m[$s] ?? 'bg-gray-100 text-gray-600';
}
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <!-- Hero -->
  <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-2xl py-14 px-8 mb-8">
    <div class="flex items-center gap-4 mb-4">
      <svg class="w-16 h-16 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
      <div>
        <h1 class="text-2xl font-bold text-white mb-1">Sistem Informasi Manajemen</h1>
        <h2 class="text-xl font-semibold text-blue-100">Pelayanan Kedatangan &amp; Keberangkatan Kapal</h2>
      </div>
    </div>
    <p class="text-blue-100 max-w-3xl mb-6 text-sm">Platform terintegrasi untuk mengelola pelayanan kapal di pelabuhan. Memudahkan koordinasi antara agen kapal, KSOP, planner, dan pandu dalam satu sistem yang efisien.</p>
    <?php if ($user): ?>
    <a href="<?= getDashboardUrl() ?>" class="inline-flex items-center gap-2 bg-white text-blue-700 font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-50 transition-colors text-sm">Ke Dashboard</a>
    <?php else: ?>
    <a href="<?= url('login.php') ?>" class="inline-flex items-center gap-2 bg-white text-blue-700 font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-50 transition-colors text-sm">Login ke Sistem</a>
    <?php endif; ?>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <?php foreach([['Kapal Hari Ini','5 Kapal','text-blue-600'],['Sedang Berlangsung','1 Kapal','text-green-600'],['Menunggu Jadwal','3 PKK','text-yellow-600']] as [$lbl,$val,$col]): ?>
    <div class="card p-6">
      <div class="flex items-center justify-between">
        <div><p class="text-gray-500 text-sm"><?=$lbl?></p><p class="text-2xl font-bold mt-1"><?=$val?></p></div>
        <svg class="w-8 h-8 <?=$col?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Jadwal -->
  <div class="card p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-lg font-semibold text-gray-900">Jadwal Pelayanan Kapal</h2>
      <span class="badge bg-blue-100 text-blue-800 border border-blue-200 text-xs"><?= count($jadwal) ?> Pelayanan</span>
    </div>
    <!-- Filter -->
    <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span class="text-gray-700 text-sm font-medium">Filter Tanggal:</span>
        </div>
        <div class="flex flex-wrap gap-2">
          <?php foreach(['2024-11-16'=>'Hari Ini','2024-11-17'=>'Besok','2024-11-18'=>'Lusa'] as $d=>$lbl): ?>
          <a href="?date=<?=$d?>" class="<?=($filterDate==$d)?'btn-primary':'btn-outline'?> text-xs py-1.5 px-3"><?=$lbl?></a>
          <?php endforeach; ?>
        </div>
        <form method="get" class="flex items-center gap-2">
          <input type="date" name="date" value="<?=htmlspecialchars($filterDate)?>" class="form-input text-sm py-1.5" style="width:auto">
          <button type="submit" class="btn-outline text-xs py-1.5 px-3">Pilih</button>
        </form>
      </div>
      <div class="mt-3 pt-3 border-t border-blue-200">
        <p class="text-sm text-gray-600">Menampilkan jadwal untuk: <span class="font-semibold text-blue-700"><?=date('d F Y',strtotime($filterDate))?></span></p>
      </div>
    </div>
    <?php if (!empty($jadwal)): ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="border-b bg-gray-50">
          <th class="text-left py-3 px-4 font-medium text-gray-600">No. Pelayanan</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Nama Kapal</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Dermaga</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Waktu</th>
          <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
        </tr></thead>
        <tbody>
          <?php foreach($jadwal as $j): ?>
          <tr class="border-b hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 font-medium text-blue-700"><?=htmlspecialchars($j['nomor_pkk'])?></td>
            <td class="py-3 px-4">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
                <?=htmlspecialchars($j['nama_kapal'])?>
              </div>
            </td>
            <td class="py-3 px-4"><?=htmlspecialchars($j['jenis_layanan'])?></td>
            <td class="py-3 px-4"><span class="badge bg-blue-50 text-blue-700 border border-blue-200"><?=htmlspecialchars($j['kode_dermaga']??'-')?></span></td>
            <td class="py-3 px-4 text-gray-600"><?=isset($j['tanggal_mulai'])?date('d/m/Y H:i',strtotime($j['tanggal_mulai'])):'-'?></td>
            <td class="py-3 px-4"><span class="badge <?=statusBadge($j['status'])?>"><?=htmlspecialchars($j['status'])?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
      <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1C7 22 7 21 9.5 21c2.6 0 2.4 1 5 1 2.5 0 2.5-1 5-1 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 3V1"/></svg>
      <p class="text-gray-500 font-medium">Tidak ada jadwal pelayanan pada tanggal ini</p>
      <p class="text-gray-400 text-sm mt-1">Silakan pilih tanggal lain</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body></html>
