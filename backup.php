<?php
session_start();
// Proteksi: Hanya boleh dijalankan oleh Admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Konfigurasi Database
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "labtrack";

// Menentukan folder penyimpanan cadangan
$backup_dir = 'backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true); // Buat folder otomatis kalau belum ada
}

$file_name = $dbname . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
$backup_file = $backup_dir . $file_name;

/* Menggunakan perintah mysqldump bawaan XAMPP untuk backup data terdistribusi.
  Sesuaikan path di bawah jika folder XAMPP kamu berada di drive selain C:
*/
$command = "C:\xampp\mysql\bin\mysqldump.exe --user=$dbuser --password=$dbpass --host=$dbhost $dbname > $backup_file";

// Jalankan perintah system OS
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo "<script>
            alert('Backup Berhasil! File disimpan di: $backup_file');
            window.location.href='dashboard.php';
          </script>";
} else {
    echo "<script>
            alert('Backup Gagal! Periksa jalur / path mysqldump XAMPP Anda.');
            window.location.href='dashboard.php';
          </script>";
}
?>