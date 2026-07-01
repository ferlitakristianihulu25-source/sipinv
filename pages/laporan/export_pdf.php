<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/auth/login.php');
    exit();
}

$tgl_awal  = mysqli_real_escape_string($koneksi, $_GET['tgl_awal']  ?? date('Y-m-01'));
$tgl_akhir = mysqli_real_escape_string($koneksi, $_GET['tgl_akhir'] ?? date('Y-m-d'));

$data_stok = mysqli_query($koneksi, "SELECT b.kode_barang, b.nama_barang,
    k.nama_kategori, b.stok, b.satuan, b.lokasi,
    COALESCE((SELECT SUM(jumlah) FROM barang_masuk
        WHERE barang_id = b.id
        AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as total_masuk,
    COALESCE((SELECT SUM(jumlah) FROM barang_keluar
        WHERE barang_id = b.id
        AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as total_keluar
    FROM barang b
    JOIN kategori k ON b.kategori_id = k.id
    ORDER BY k.nama_kategori, b.nama_barang");

$total_masuk_all = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(jumlah),0) as total FROM barang_masuk
     WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'"))['total'];

$total_keluar_all = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(jumlah),0) as total FROM barang_keluar
     WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'"))['total'];

$total_stok_all = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(stok),0) as total FROM barang"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris — PT Salim Ivomas Pratama</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a5c2a; padding-bottom: 12px; margin-bottom: 16px; }
        .header h2 { font-size: 14px; color: #1a5c2a; margin-bottom: 4px; }
        .header h3 { font-size: 12px; margin-bottom: 2px; }
        .header p  { font-size: 10px; color: #666; }
        .info-row  { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead th { background: #1a5c2a; color: white; padding: 7px 6px; text-align: left; font-size: 10px; }
        tbody td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        tfoot td { padding: 7px 6px; background: #f0f7f2; font-weight: bold; border-top: 2px solid #1a5c2a; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .badge-masuk  { color: #198754; font-weight: bold; }
        .badge-keluar { color: #dc3545; font-weight: bold; }
        .badge-stok   { color: #0d6efd; font-weight: bold; }
        .footer { margin-top: 20px; display: flex; justify-content: flex-end; }
        .ttd { text-align: center; width: 200px; }
        .ttd .garis { border-top: 1px solid #333; margin-top: 50px; padding-top: 4px; font-size: 10px; }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<!-- TOMBOL PRINT -->
<div class="no-print" style="padding:12px; background:#f8f9fa; margin-bottom:10px;">
    <button onclick="window.print()"
        style="background:#dc3545;color:white;border:none;padding:8px 20px;
               border-radius:6px;cursor:pointer;font-size:12px;">
        <b>🖨 Cetak / Simpan PDF</b>
    </button>
    <button onclick="window.close()"
        style="background:#6c757d;color:white;border:none;padding:8px 16px;
               border-radius:6px;cursor:pointer;font-size:12px;margin-left:8px;">
        Tutup
    </button>
</div>

<!-- HEADER LAPORAN -->
<div style="padding: 20px 30px;">
    <div class="header">
        <h2>LAPORAN INVENTARIS BARANG</h2>
        <h3>PT Salim Ivomas Pratama</h3>
        <p>Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> 
           s/d <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <div class="info-row">
        <div>
            <b>Dicetak oleh:</b> <?= htmlspecialchars($_SESSION['user_nama']) ?><br>
            <b>Jabatan:</b> <?= ucfirst($_SESSION['user_role']) ?><br>
            <b>Tanggal cetak:</b> <?= date('d F Y, H:i') ?> WIB
        </div>
        <div style="text-align:right;">
            <b>Total Jenis Barang:</b> <?= mysqli_num_rows($data_stok) ?> jenis<br>
            <b>Total Stok:</b> <?= $total_stok_all ?> unit<br>
            <b>Barang Masuk:</b> +<?= $total_masuk_all ?> unit<br>
            <b>Barang Keluar:</b> -<?= $total_keluar_all ?> unit
        </div>
    </div>

    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th style="width:70px">Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th class="text-center" style="width:60px">Masuk</th>
                <th class="text-center" style="width:60px">Keluar</th>
                <th class="text-center" style="width:60px">Stok</th>
                <th style="width:40px">Sat.</th>
                <th>Lokasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            // Reset pointer karena sudah dipakai di atas
            mysqli_data_seek($data_stok, 0);
            while ($row = mysqli_fetch_assoc($data_stok)):
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td class="text-center badge-masuk">
                    <?= $row['total_masuk'] > 0 ? '+' . $row['total_masuk'] : '—' ?>
                </td>
                <td class="text-center badge-keluar">
                    <?= $row['total_keluar'] > 0 ? '-' . $row['total_keluar'] : '—' ?>
                </td>
                <td class="text-center badge-stok"><?= $row['stok'] ?></td>
                <td><?= htmlspecialchars($row['satuan']) ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total Keseluruhan:</td>
                <td class="text-center badge-masuk">+<?= $total_masuk_all ?></td>
                <td class="text-center badge-keluar">-<?= $total_keluar_all ?></td>
                <td class="text-center badge-stok"><?= $total_stok_all ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer">
        <div class="ttd">
            <p>Pekanbaru, <?= date('d F Y') ?></p>
            <p>Mengetahui,</p>
            <div class="garis">
                <b><?= htmlspecialchars($_SESSION['user_nama']) ?></b><br>
                <?= ucfirst($_SESSION['user_role']) ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>