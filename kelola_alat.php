<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Proses Tambah Alat Baru
if (isset($_POST['tambah_alat'])) {
    $nama_alat = mysqli_real_escape_string($conn, $_POST['nama_alat']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $query = "INSERT INTO inventaris (nama_alat, stok, deskripsi) VALUES ('$nama_alat', '$stok', '$deskripsi')";
    if (mysqli_query($conn, $query)) {
        $success = "Alat baru berhasil ditambahkan ke inventaris laboratorium!";
    }
}

// Ambil data untuk ditampilkan ke tabel
$katalog = mysqli_query($conn, "SELECT * FROM inventaris");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventaris - LabTrack</title>
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
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <h5 class="fw-bold text-primary mb-3">Tambah Alat Baru</h5>
                <hr>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Alat Lab</label>
                        <input type="text" class="form-control" name="nama_alat" placeholder="Contoh: Oscilloscope" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Jumlah Stok Awal</label>
                        <input type="number" class="form-control" name="stok" min="0" placeholder="Contoh: 10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Deskripsi / Spesifikasi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Tulis info detail alat..." required></textarea>
                    </div>
                    <button type="submit" name="tambah_alat" class="btn btn-primary w-100 fw-bold">Simpan ke Gudang</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <h5 class="fw-bold text-dark mb-3">Data Stok Fisik Inventaris</h5>
                
                <?php if(isset($success)) echo "<div class='alert alert-success py-2 small'>$success</div>"; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary small text-uppercase">
                            <tr>
                                <th>Nama Alat</th>
                                <th class="text-center">Ketersediaan</th>
                                <th>Deskripsi Alat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($katalog)): ?>
                            <tr>
                                <td class="fw-bold text-secondary"><?php echo $row['nama_alat']; ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($row['stok'] > 0) ? 'bg-success' : 'bg-danger'; ?> py-2 px-3">
                                        <?php echo $row['stok']; ?> Unit
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo $row['deskripsi']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>