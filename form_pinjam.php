<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

if (isset($_POST['ajukan_pinjam'])) {
    $id_alat = mysqli_real_escape_string($conn, $_POST['id_alat']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tgl_pinjam = date('Y-m-d');
    $tgl_kembali = mysqli_real_escape_string($conn, $_POST['tgl_kembali']);

    // =========================================================
    // IMPLEMENTASI MATERI: DATABASE TRANSACTION (START)
    // =========================================================
    mysqli_begin_transaction($conn);

    try {
        // Cek stok dengan fitur Row Locking (FOR UPDATE) demi keamanan data terdistribusi
        $cek_stok = mysqli_query($conn, "SELECT stok FROM inventaris WHERE id_alat = '$id_alat' FOR UPDATE");
        $data_stok = mysqli_fetch_assoc($cek_stok);

        if ($jumlah > $data_stok['stok']) {
            throw new Exception("Gagal mengajukan! Jumlah melebihi sisa stok laboratorium.");
        } elseif ($jumlah <= 0) {
            throw new Exception("Gagal mengajukan! Jumlah pinjam minimal 1 pcs.");
        }

        // Jalankan Query Insert Data Transaksi
        $query = "INSERT INTO peminjaman (id_user, id_alat, jumlah, tgl_pinjam, tgl_kembali, status) 
                  VALUES ('$id_user', '$id_alat', '$jumlah', '$tgl_pinjam', '$tgl_kembali', 'menunggu')";
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Gagal menyimpan data transaksi ke database.");
        }

        // Jika semua operasi sukses, simpan secara permanen
        mysqli_commit($conn);
        $success = "Pengajuan berhasil dikirim via Secure Database Transaction!";
    } catch (Exception $e) {
        // Jika ada kesalahan/stok habis, batalkan semua perubahan data
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
    // =========================================================
    // IMPLEMENTASI MATERI: DATABASE TRANSACTION (END)
    // =========================================================
}

$katalog = mysqli_query($conn, "SELECT * FROM inventaris");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Peminjaman Alat - LabTrack</title>
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

<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
        <h4 class="fw-bold text-primary mb-2">Formulir Peminjaman</h4>
        <p class="text-muted small">Silakan masukkan jenis alat lab dan tanggal pengembalian dengan benar.</p>
        <hr>
        
        <?php if(isset($error)) echo "<div class='alert alert-danger py-2 small'>$error</div>"; ?>
        <?php if(isset($success)) echo "<div class='alert alert-success py-2 small'>$success</div>"; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="id_alat" class="form-label small fw-semibold text-secondary">Pilih Alat Praktikum</label>
                <select class="form-select" id="id_alat" name="id_alat" required>
                    <option value="" selected disabled>-- Pilih Alat --</option>
                    <?php while($row = mysqli_fetch_assoc($katalog)): ?>
                        <option value="<?php echo $row['id_alat']; ?>" <?php echo ($row['stok'] <= 0) ? 'disabled class="text-danger"' : ''; ?>>
                            <?php echo $row['nama_alat']; ?> <?php echo ($row['stok'] <= 0) ? '(Stok Habis)' : '(Sisa: ' . $row['stok'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jumlah" class="form-label small fw-semibold text-secondary">Jumlah yang Dipinjam</label>
                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" placeholder="Contoh: 2" required>
            </div>
            
            <div class="mb-4">
                <label for="tgl_kembali" class="form-label small fw-semibold text-secondary">Tanggal Pengembalian</label>
                <input type="date" class="form-control" id="tgl_kembali" name="tgl_kembali" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <button type="submit" name="ajukan_pinjam" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Kirim Formulir Pengajuan</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>