<?php
function statusBadgePKK($s) {
    $m=['Menunggu Validasi'=>'bg-yellow-100 text-yellow-800','Divalidasi'=>'bg-green-100 text-green-800','Ditolak'=>'bg-red-100 text-red-800','Dijadwalkan'=>'bg-blue-100 text-blue-800','Selesai'=>'bg-gray-100 text-gray-700'];
    return $m[$s]??'bg-gray-100 text-gray-600';
}
function statusBadgeJadwal($s) {
    $m=['Draft'=>'bg-gray-100 text-gray-600','Dikonfirmasi'=>'bg-purple-100 text-purple-800','Berlangsung'=>'bg-green-100 text-green-800','Selesai'=>'bg-gray-100 text-gray-700','Dibatalkan'=>'bg-red-100 text-red-800','Ditunda'=>'bg-yellow-100 text-yellow-800'];
    return $m[$s]??'bg-gray-100 text-gray-600';
}
function statusBadgePembayaran($s) {
    $m=['Menunggu'=>'bg-yellow-100 text-yellow-800','Lunas'=>'bg-green-100 text-green-800','Gagal'=>'bg-red-100 text-red-800'];
    return $m[$s]??'bg-gray-100 text-gray-600';
}
function statusBadgeLaporan($s) {
    $m=['Draft'=>'bg-gray-100 text-gray-600','Dikirim'=>'bg-blue-100 text-blue-800','Ditinjau'=>'bg-purple-100 text-purple-800','Disetujui'=>'bg-green-100 text-green-800','Perlu Revisi'=>'bg-yellow-100 text-yellow-800'];
    return $m[$s]??'bg-gray-100 text-gray-600';
}
function formatRupiah($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function backBtn($url, $text='Kembali') {
    echo '<a href="'.$url.'" class="inline-flex items-center text-blue-600 hover:underline mb-4 text-sm">
      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      '.$text.'</a>';
}
