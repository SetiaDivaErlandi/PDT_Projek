<?php
include 'config/db.php';
session_start();

// Keamanan: Hanya Admin yang boleh masuk ke halaman ini
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Proses Hapus Akun Mahasiswa
if (isset($_GET['hapus'])) {
    $id_user = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Pastikan admin tidak bisa menghapus sesama admin
    $query_hapus = "DELETE FROM users WHERE id_user = '$id_user' AND role != 'admin'";
    if (mysqli_query($conn, $query_hapus)) {
        $success = "Akun mahasiswa berhasil dihapus dari sistem.";
    }
}

// Ambil semua data user yang rolenya BUKAN admin (berarti mahasiswa/user biasa)
$users_query = mysqli_query($conn, "SELECT id_user, username, role FROM users WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-sm btn-outline-light px-3">Kembali ke Dashboard</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="card p-4 shadow-sm border-0 bg-white">
        <h3 class="fw-bold text-warning m-0">Manajemen Kontrol User</h3>
        <p class="text-muted small mt-1 mb-4">Pantau status akun teridstribusi dan hapus hak akses login mahasiswa jika diperlukan.</p>

        <?php if(isset($success)): ?>
            <div class="alert alert-success small mb-4 py-2 px-3"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle text-center border-light">
                <thead class="table-dark text-uppercase small">
                    <tr>
                        <th style="width: 20%;">ID User</th>
                        <th style="width: 40%;">Username / Nama Mahasiswa</th>
                        <th style="width: 20%;">Role Akses</th>
                        <th style="width: 20%;">Aksi Kontrol</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if(mysqli_num_rows($users_query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($users_query)): ?>
                        <tr>
                            <td class="fw-bold text-secondary">#USR-0<?php echo $row['id_user']; ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><span class="badge bg-secondary px-3 py-1"><?php echo $row['role']; ?></span></td>
                            <td>
                                <a href="kelola_user.php?hapus=<?php echo $row['id_user']; ?>" 
                                   class="btn btn-sm btn-danger fw-bold py-1 px-3" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus akun mahasiswa bernama <?php echo $row['username']; ?>?')">
                                   Hapus Akun
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-muted py-4">Belum ada data mahasiswa yang terdaftar di database.</td>
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