<?php
// admin/cetak.php
require_once '../config/connection.php';
require_once '../config/session.php';
require_once '../config/helpers.php';
check_admin();

$filter_bulan = sanitize($_GET['bulan'] ?? date('m'));
$filter_tahun = sanitize($_GET['tahun'] ?? date('Y'));
$filter_status = sanitize($_GET['status'] ?? '');
$format = sanitize($_GET['format'] ?? 'print');

$whereClauses = ["1=1"];
$params = [];

if (!empty($filter_bulan)) {
    $whereClauses[] = "MONTH(a.tanggal) = :bulan";
    $params['bulan'] = $filter_bulan;
}
if (!empty($filter_tahun)) {
    $whereClauses[] = "YEAR(a.tanggal) = :tahun";
    $params['tahun'] = $filter_tahun;
}
if (!empty($filter_status)) {
    $whereClauses[] = "a.status = :status";
    $params['status'] = $filter_status;
}

$whereSql = implode(" AND ", $whereClauses);

try {
    $stmt = $pdo->prepare("
        SELECT a.*, m.nim, m.nama, k.nama_kelas 
        FROM absensi a
        JOIN mahasiswa m ON a.mahasiswa_id = m.id
        LEFT JOIN kelas k ON m.kelas_id = k.id
        WHERE $whereSql
        ORDER BY a.tanggal DESC, m.nama ASC
    ");
    $stmt->execute($params);
    $laporan = $stmt->fetchAll();
    
    $stmtSet = $pdo->query("SELECT * FROM pengaturan LIMIT 1");
    $settings = $stmtSet->fetch();
} catch (\Exception $e) {
    die("Error fetching data: " . $e->getMessage());
}

$bulans = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$namaBulan = $bulans[(int)$filter_bulan];
$title = "Laporan Absensi Mahasiswa - $namaBulan $filter_tahun";
if ($filter_status) $title .= " (Status: $filter_status)";

if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"Laporan_Absensi_{$namaBulan}_{$filter_tahun}.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; }
        .header p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .footer-ttd { width: 300px; float: right; text-align: center; margin-top: 30px; }
    </style>
</head>
<body <?= $format === 'print' ? 'onload="window.print()"' : '' ?>>
    <div class="header">
        <h2><?= sanitize($settings['nama_kampus'] ?? 'UNIVERSITAS') ?></h2>
        <p><?= sanitize($settings['alamat_kampus'] ?? 'Alamat Kampus') ?></p>
        <p><strong>REKAPITULASI KEHADIRAN MAHASISWA</strong></p>
        <p>Periode: <?= $namaBulan ?> <?= $filter_tahun ?> <?= $filter_status ? "| Status: $filter_status" : "" ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Kelas</th>
                <th class="text-center">Jam Masuk</th>
                <th class="text-center">Jam Pulang</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($laporan)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            <?php else: $no=1; foreach($laporan as $lap): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= date('d-m-Y', strtotime($lap['tanggal'])) ?></td>
                    <td><?= sanitize($lap['nim']) ?></td>
                    <td><?= sanitize($lap['nama']) ?></td>
                    <td><?= sanitize($lap['nama_kelas']) ?></td>
                    <td class="text-center"><?= $lap['jam_masuk'] ? date('H:i', strtotime($lap['jam_masuk'])) : '-' ?></td>
                    <td class="text-center"><?= $lap['jam_pulang'] ? date('H:i', strtotime($lap['jam_pulang'])) : '-' ?></td>
                    <td class="text-center"><?= sanitize($lap['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <?php if ($format === 'print'): ?>
        <div class="footer-ttd">
            <p>Mengetahui,</p>
            <p style="margin-bottom: 70px;">Administrator</p>
            <p><strong><?= sanitize($_SESSION['nama']) ?></strong></p>
        </div>
    <?php endif; ?>
</body>
</html>
