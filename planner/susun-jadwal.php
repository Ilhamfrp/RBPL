<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireRole('Planner');
$user = getUser();
$pageTitle = 'Susun Jadwal';

$pkkId   = intval($_GET['pkk_id'] ?? 0);
$errors  = [];
$success = false;
$bentrok = [];

// Ambil daftar PKK yang sudah divalidasi (siap dijadwalkan)
try {
    $pkkList = fetchAll("SELECT p.*, k.nama_kapal, k.jenis_kapal, u.nama_lengkap as nama_agen
                         FROM pkk p
                         JOIN kapal k ON p.kapal_id = k.id
                         JOIN users u ON p.agen_id  = u.id
                         WHERE p.status = 'Divalidasi'
                         ORDER BY p.tanggal_ajuan DESC");
} catch (Exception $e) { $pkkList = []; }

// Ambil detail PKK yang dipilih
$selectedPKK = null;
if ($pkkId) {
    try {
        $selectedPKK = fetchOne("SELECT p.*, k.nama_kapal, k.jenis_kapal, k.gt, u.nama_lengkap as nama_agen
                                  FROM pkk p
                                  JOIN kapal k ON p.kapal_id = k.id
                                  JOIN users u ON p.agen_id  = u.id
                                  WHERE p.id = ? AND p.status = 'Divalidasi'", [$pkkId]);
    } catch (Exception $e) {}
}

// Ambil daftar pandu
try {
    $panduList = fetchAll("SELECT u.id, u.nama_lengkap, u.username
                           FROM users u JOIN roles r ON u.role_id = r.id
                           WHERE r.nama_role = 'Pandu' AND u.is_active = 1
                           ORDER BY u.nama_lengkap");
} catch (Exception $e) { $panduList = []; }

// Ambil daftar dermaga
try {
    $dermagaList = fetchAll("SELECT * FROM dermaga ORDER BY kode_dermaga");
} catch (Exception $e) { $dermagaList = []; }

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action              = $_POST['action'] ?? '';
    $pkkId               = intval($_POST['pkk_id'] ?? $pkkId);
    $tglMulai            = ($_POST['tgl_mulai']  ?? '') ?: null;
    $tglSelesai          = ($_POST['tgl_selesai'] ?? '') ?: null;
    $dermagaId           = intval($_POST['dermaga_id'] ?? 0);
    $panduId             = intval($_POST['pandu_id']   ?? 0) ?: null;
    $hasilKom            = $_POST['hasil_komunikasi']   ?? '';
    $catatanKom          = trim($_POST['catatan_komunikasi'] ?? '');
    $catatan             = trim($_POST['catatan'] ?? '');

    // Reload selectedPKK dari POST pkkId
    if ($pkkId && !$selectedPKK) {
        try {
            $selectedPKK = fetchOne("SELECT p.*, k.nama_kapal, k.jenis_kapal, k.gt, u.nama_lengkap as nama_agen
                                      FROM pkk p JOIN kapal k ON p.kapal_id=k.id JOIN users u ON p.agen_id=u.id
                                      WHERE p.id=?", [$pkkId]);
        } catch(Exception $e) {}
    }

    if ($action === 'cek_bentrok') {
        // Cek tumpang-tindih jadwal di dermaga yang sama
        if ($dermagaId && $tglMulai) {
            try {
                $tglSelesaiCek = $tglSelesai ?: date('Y-m-d H:i:s', strtotime($tglMulai) + 4*3600);
                $bentrok = fetchAll(
                    "SELECT j.*, p.nomor_pkk, k.nama_kapal, d.kode_dermaga
                     FROM jadwal_pelayanan j
                     JOIN pkk p   ON j.pkk_id    = p.id
                     JOIN kapal k ON p.kapal_id  = k.id
                     JOIN dermaga d ON j.dermaga_id = d.id
                     WHERE j.dermaga_id = ?
                       AND j.status NOT IN ('Dibatalkan','Selesai')
                       AND j.tanggal_mulai  < ?
                       AND (j.tanggal_selesai IS NULL OR j.tanggal_selesai > ?)",
                    [$dermagaId, $tglSelesaiCek, $tglMulai]
                );
            } catch (Exception $e) {}
        }
    } elseif ($action === 'simpan' || $action === 'konfirmasi') {
        if (!$pkkId)     $errors[] = 'PKK harus dipilih';
        if (!$tglMulai)  $errors[] = 'Waktu mulai harus diisi';
        if (!$dermagaId) $errors[] = 'Dermaga harus dipilih';

        if (empty($errors)) {
            try {
                $statusJadwal = ($action === 'konfirmasi') ? 'Dikonfirmasi' : 'Draft';
                if ($hasilKom === 'tidak') $statusJadwal = 'Ditunda';

                $catatanFull = trim(
                    ($catatan ? $catatan : '') .
                    ($catatanKom ? "\nKomunikasi agen: $catatanKom" : '')
                );

                query("INSERT INTO jadwal_pelayanan
                           (pkk_id, planner_id, dermaga_id, tanggal_mulai, tanggal_selesai, status, catatan)
                       VALUES (?,?,?,?,?,?,?)",
                    [$pkkId, $user['id'], $dermagaId, $tglMulai, $tglSelesai, $statusJadwal, $catatanFull ?: null]
                );
                $jadwalId = getDB()->lastInsertId();

                // Alokasi pandu jika dipilih
                if ($panduId) {
                    $pNama = fetchOne("SELECT nama_lengkap FROM users WHERE id=?", [$panduId])['nama_lengkap'] ?? '-';
                    query("INSERT INTO alokasi_sumber_daya
                               (jadwal_id, pandu_id, jenis_sumber_daya, nama_sumber_daya, status)
                           VALUES (?,?,'Pandu',?,?)",
                        [$jadwalId, $panduId, $pNama, 'Dialokasikan']
                    );
                }

                // Update status PKK
                $newPkkStatus = ($statusJadwal === 'Ditunda') ? 'Divalidasi' : 'Dijadwalkan';
                query("UPDATE pkk SET status=? WHERE id=?", [$newPkkStatus, $pkkId]);

                // Notifikasi ke agen
                if ($selectedPKK) {
                    $msg = ($statusJadwal === 'Ditunda')
                        ? "Jadwal PKK {$selectedPKK['nomor_pkk']} ditunda. Planner akan menghubungi Anda."
                        : "Jadwal pelayanan untuk PKK {$selectedPKK['nomor_pkk']} telah $statusJadwal.";
                    query("INSERT INTO notifikasi (user_id, judul, pesan, jenis) VALUES (?,?,?,'Info')",
                        [$selectedPKK['agen_id'], 'Update Jadwal PKK', $msg]
                    );
                }

                $success = $statusJadwal;
            } catch (Exception $e) {
                $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <?php backBtn('dashboard.php', 'Dashboard Planner'); ?>
    <h1 class="text-2xl font-bold mb-1">Susun Jadwal Pelayanan</h1>
    <p class="text-gray-500 text-sm">Buat draft jadwal dan alokasi sumber daya</p>
  </div>

  <?php if ($success): ?>
  <div class="alert-success mb-6">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="text-sm font-medium">
      <?= $success === 'Ditunda' ? 'Jadwal disimpan dengan status Ditunda. Hubungi agen untuk konfirmasi ulang.' : ($success === 'Dikonfirmasi' ? 'Jadwal berhasil dikonfirmasi!' : 'Draft jadwal berhasil disimpan.') ?>
    </span>
  </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
  <div class="alert-error mb-6"><ul class="text-sm list-disc list-inside"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <?php if (!empty($bentrok)): ?>
  <div class="alert-warning mb-6 block">
    <div class="flex items-start gap-3">
      <svg class="w-5 h-5 flex-shrink-0 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <div>
        <p class="font-semibold text-yellow-800">Terdeteksi Bentrok Jadwal!</p>
        <?php foreach($bentrok as $b): ?>
        <p class="text-sm text-yellow-700 mt-1">• <?=htmlspecialchars($b['nama_kapal'])?> (<?=htmlspecialchars($b['nomor_pkk'])?>) — Dermaga <?=htmlspecialchars($b['kode_dermaga'])?> pada <?=date('d/m/Y H:i', strtotime($b['tanggal_mulai']))?></p>
        <?php endforeach; ?>
        <p class="text-sm text-yellow-700 mt-2">Silakan pilih dermaga lain, atau ubah waktu dan konfirmasi ulang dengan agen.</p>
      </div>
    </div>
  </div>
  <?php elseif ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='cek_bentrok'): ?>
  <div class="alert-success mb-6">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="text-sm font-medium">Tidak ada bentrok jadwal. Dermaga tersedia untuk waktu yang dipilih.</span>
  </div>
  <?php endif; ?>

  <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <input type="hidden" name="pkk_id" value="<?=$pkkId?>">
    <div class="lg:col-span-2 space-y-6">

      <!-- Pilih PKK -->
      <div class="card p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Pilih PKK
        </h3>
        <select name="pkk_id" class="form-input" onchange="this.form.submit()" required>
          <option value="">-- Pilih PKK Tervalidasi --</option>
          <?php foreach($pkkList as $p): ?>
          <option value="<?=$p['id']?>" <?=$p['id']==$pkkId?'selected':''?>><?=htmlspecialchars($p['nomor_pkk'])?> — <?=htmlspecialchars($p['nama_kapal'])?></option>
          <?php endforeach; ?>
        </select>
        <?php if(empty($pkkList)): ?>
        <p class="text-sm text-yellow-700 bg-yellow-50 p-3 rounded-lg mt-3">Belum ada PKK yang divalidasi. Tunggu KSOP memvalidasi permohonan.</p>
        <?php endif; ?>
        <?php if($selectedPKK): ?>
        <div class="mt-3 p-4 bg-blue-50 rounded-lg border border-blue-200 grid grid-cols-2 gap-3 text-sm">
          <div><p class="text-xs text-gray-500">Kapal</p><p class="font-medium"><?=htmlspecialchars($selectedPKK['nama_kapal'])?></p></div>
          <div><p class="text-xs text-gray-500">Jenis</p><p class="font-medium"><?=htmlspecialchars($selectedPKK['jenis_kapal'])?></p></div>
          <div><p class="text-xs text-gray-500">GT</p><p class="font-medium"><?=number_format($selectedPKK['gt'])?>  GT</p></div>
          <div><p class="text-xs text-gray-500">Dermaga Diminta</p><p class="font-medium"><?=htmlspecialchars($selectedPKK['dermaga_diminta']??'-')?></p></div>
          <div><p class="text-xs text-gray-500">Jenis Layanan</p><p class="font-medium"><?=htmlspecialchars($selectedPKK['jenis_layanan'])?></p></div>
          <div><p class="text-xs text-gray-500">Agen</p><p class="font-medium"><?=htmlspecialchars($selectedPKK['nama_agen'])?></p></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Jadwal & Dermaga -->
      <div class="card p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Jadwal Pelayanan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div><label class="form-label">Waktu Mulai <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="tgl_mulai" class="form-input" value="<?=htmlspecialchars($_POST['tgl_mulai']??'')?>" required></div>
          <div><label class="form-label">Waktu Selesai</label>
            <input type="datetime-local" name="tgl_selesai" class="form-input" value="<?=htmlspecialchars($_POST['tgl_selesai']?? '')?>"></div>
        </div>
        <div class="mb-4">
          <label class="form-label">Dermaga <span class="text-red-500">*</span></label>
          <select name="dermaga_id" class="form-input" required>
            <option value="">-- Pilih Dermaga --</option>
            <?php foreach($dermagaList as $d): ?>
            <option value="<?=$d['id']?>" <?=(($_POST['dermaga_id']??0)==$d['id'])?'selected':''?>
              <?=$d['status']==='Maintenance'?'disabled':''?>>
              <?=htmlspecialchars($d['kode_dermaga'])?> — <?=htmlspecialchars($d['nama_dermaga']??'')?> (<?=htmlspecialchars($d['status'])?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="action" value="cek_bentrok"
                class="btn-outline text-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Cek Ketersediaan & Bentrok
        </button>
      </div>

      <!-- Alokasi Pandu -->
      <div class="card p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
          Alokasi Pandu
        </h3>
        <label class="form-label">Pandu Ditugaskan</label>
        <select name="pandu_id" class="form-input">
          <option value="">-- Pilih Pandu (opsional) --</option>
          <?php foreach($panduList as $p): ?>
          <option value="<?=$p['id']?>" <?=(($_POST['pandu_id']??0)==$p['id'])?'selected':''?>><?=htmlspecialchars($p['nama_lengkap']?:$p['username'])?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Konfirmasi Agen -->
      <div class="card p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/></svg>
          Komunikasi dengan Agen
        </h3>
        <div class="mb-4">
          <label class="form-label">Hasil Komunikasi dengan Agen</label>
          <div class="flex gap-6 mt-2">
            <label class="flex items-center gap-2 cursor-pointer text-sm">
              <input type="radio" name="hasil_komunikasi" value="setuju" <?=(($_POST['hasil_komunikasi']??'')==='setuju')?'checked':''?>>
              <span class="text-green-700 font-medium">Agen Setuju</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm">
              <input type="radio" name="hasil_komunikasi" value="tidak" <?=(($_POST['hasil_komunikasi']??'')==='tidak')?'checked':''?>>
              <span class="text-red-700 font-medium">Agen Tidak Setuju → Status Ditunda</span>
            </label>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Catatan Komunikasi</label>
          <textarea name="catatan_komunikasi" rows="2" class="form-input" placeholder="Ringkasan komunikasi dengan agen..."><?=htmlspecialchars($_POST['catatan_komunikasi']??'')?></textarea>
        </div>
        <div>
          <label class="form-label">Catatan Planner</label>
          <textarea name="catatan" rows="2" class="form-input" placeholder="Catatan tambahan untuk jadwal ini..."><?=htmlspecialchars($_POST['catatan']??'')?></textarea>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-4 self-start">
      <div class="card p-6">
        <h3 class="font-semibold mb-4">Simpan Jadwal</h3>
        <div class="space-y-3">
          <button type="submit" name="action" value="simpan" class="btn-outline w-full justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            Simpan sebagai Draft
          </button>
          <button type="submit" name="action" value="konfirmasi" class="btn-primary w-full justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Konfirmasi Jadwal
          </button>
          <a href="dashboard.php" class="btn-outline w-full justify-center">Batal</a>
        </div>
      </div>

      <div class="card p-6">
        <h3 class="font-semibold text-sm mb-3">Alur Kerja</h3>
        <ol class="space-y-2 text-xs text-gray-600">
          <?php foreach(['Pilih PKK tervalidasi','Isi waktu mulai &amp; selesai','Pilih dermaga','Klik Cek Bentrok','Jika tidak bentrok → Konfirmasi','Jika bentrok → Hubungi agen, pilih slot lain','Jika agen tidak setuju → Pilih "Tidak Setuju"'] as $i=>$s): ?>
          <li class="flex items-start gap-2"><span class="bg-blue-100 text-blue-700 rounded-full w-4 h-4 flex items-center justify-center flex-shrink-0 font-bold"><?=$i+1?></span><?=$s?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <?php
      try { $jadwalAktif=fetchAll("SELECT j.*,k.nama_kapal,d.kode_dermaga FROM jadwal_pelayanan j JOIN pkk p ON j.pkk_id=p.id JOIN kapal k ON p.kapal_id=k.id LEFT JOIN dermaga d ON j.dermaga_id=d.id WHERE j.status NOT IN ('Dibatalkan','Selesai') ORDER BY j.tanggal_mulai LIMIT 5"); }
      catch(Exception $e){ $jadwalAktif=[]; }
      ?>
      <?php if(!empty($jadwalAktif)): ?>
      <div class="card p-6">
        <h3 class="font-semibold text-sm mb-3">Jadwal Aktif</h3>
        <div class="space-y-2">
          <?php foreach($jadwalAktif as $ja): ?>
          <div class="text-xs bg-gray-50 p-2 rounded border">
            <p class="font-medium"><?=htmlspecialchars($ja['nama_kapal'])?></p>
            <p class="text-gray-500"><?=htmlspecialchars($ja['kode_dermaga']??'-')?> | <?=date('d/m H:i',strtotime($ja['tanggal_mulai']))?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
