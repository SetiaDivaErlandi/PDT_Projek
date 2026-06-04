<?php
include 'config/db.php';
session_start();

// Proteksi halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Logika Proses Validasi Admin
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'setujui') {
        $query = "UPDATE peminjaman SET status = 'dipinjam' WHERE id_pinjam = '$id_pinjam'";
        if (mysqli_query($conn, $query)) {
            $success = "Pengajuan berhasil disetujui! Stok alat otomatis terpotong oleh TRIGGER.";
        }
    } elseif ($action == 'kembali') {
        $query = "UPDATE peminjaman SET status = 'kembali' WHERE id_pinjam = '$id_pinjam'";
        if (mysqli_query($conn, $query)) {
            $success = "Alat dikembalikan! Stok otomatis dipulangkan oleh TRIGGER.";
        }
    }
}

$query_request = "SELECT *, hitung_total_pinjam(id_user) as total_aktif FROM view_laporan_peminjaman ORDER BY id_pinjam DESC";
$requests = mysqli_query($conn, $query_request);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Peminjaman - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <a href="dashboard.php" class="btn btn-sm btn-outline-light">Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container my-5">
    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
        <h4 class="fw-bold text-success mb-2">Validasi & Request Peminjaman</h4>
        <p class="text-muted small mb-4">Memantau data terintegrasi menggunakan Database View dan Stored Function.</p>

        <?php if(isset($success)) echo "<div class='alert alert-success py-2 small'>$success</div>"; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark small text-uppercase">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Alat Praktikum</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jam Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Total Pinjam Aktif</th>
                        <th>Status Saat Ini</th>
                        <th>Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($requests) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($requests)): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo $row['nama_mahasiswa']; ?></td>
                            <td class="text-start"><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['jumlah']; ?> Pcs</td>
                            
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td><?php echo date('H:i', strtotime($row['tgl_pinjam'])); ?></td>
                            
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_kembali'])); ?></td>
                            <td><span class="badge bg-dark"><?php echo $row['total_aktif']; ?> Item Aktif</span></td>
                            <td>
                                <?php 
                                if($row['status'] == 'menunggu') {
                                    echo '<span class="badge bg-warning text-dark py-2 px-3">Menunggu</span>';
                                } elseif($row['status'] == 'dipinjam') {
                                    echo '<span class="badge bg-primary py-2 px-3">Dipinjam</span>';
                                } elseif($row['status'] == 'kembali') {
                                    echo '<span class="badge bg-success py-2 px-3">Kembali</span>';
                                } else {
                                    echo '<span class="badge bg-danger py-2 px-3">Terlambat</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'menunggu'): ?>
                                    <a href="kelola.php?action=setujui&id=<?php echo $row['id_pinjam']; ?>" class="btn btn-sm btn-success fw-bold px-2" onclick="return confirm('Setujui peminjaman ini?')">Setujui</a>
                                <?php elseif($row['status'] == 'dipinjam' || $row['status'] == 'terlambat'): ?>
                                    <a href="kelola.php?action=kembali&id=<?php echo $row['id_pinjam']; ?>" class="btn btn-sm btn-info text-white fw-bold px-2" onclick="return confirm('Selesaikan peminjaman?')">Selesai</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat transaksi pengajuan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>