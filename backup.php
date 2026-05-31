<?php
session_start();

// Proteksi halaman admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Konfigurasi Database Terdistribusi LabTrack
$host     = "localhost";
$username = "root";
$password = ""; // Kosongkan jika menggunakan bawaan XAMPP
$dbname   = "labtrack";

// 1. Pastikan folder 'backups' sudah ada secara fisik
$backup_dir = 'backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// 2. Format nama file backup dinamis berdasarkan waktu nyata
$file_name   = $dbname . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
$backup_file = $backup_dir . $file_name;

// 3. DETEKSI OTOMATIS LOKASI MYSQLDUMP XAMPP
$mysqldump_path = "";

// Daftar jalur potensial tempat XAMPP diinstal di laptopmu
$possible_paths = [
    "C:/xampp/mysql/bin/mysqldump.exe",
    "D:/xampp/mysql/bin/mysqldump.exe",
    "E:/xampp/mysql/bin/mysqldump.exe",
    "mysqldump" // Jika sudah terdaftar di Environment Variables Windows
];

foreach ($possible_paths as $path) {
    if ($path === "mysqldump" || file_exists($path)) {
        $mysqldump_path = $path;
        break;
    }
}

// Jika tetap tidak ketemu, kita tembak paksa ke default C:
if (empty($mysqldump_path)) {
    $mysqldump_path = "C:/xampp/mysql/bin/mysqldump.exe";
}

// Menyusun perintah CLI untuk ekspor database secara aman
$command = "\"$mysqldump_path\" --user=$username --password=$password --host=$host $dbname > \"$backup_file\" 2>&1";

// Menjalankan perintah sistem eksternal
exec($command, $output, $return_var);

// 4. Validasi akhir: Apakah file benar-benar tercipta di folder dan ukurannya > 0 byte?
if (file_exists($backup_file) && filesize($backup_file) > 0) {
    echo "<script>
        alert('Backup Berhasil! File fisik database nyata disimpan di: " . $backup_file . "');
        window.location.href = 'dashboard.php';
    </script>";
} else {
    // Jika masih gagal, kita pakai trik alternatif PHP murni tanpa mysqldump!
    // Ambil data lewat query biasa lalu tulis manual ke file .sql
    $tables = array();
    include 'config/db.php';
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    $sql_content = "-- LabTrack Database Backup via PHP Fallback\n\n";
    foreach ($tables as $table) {
        // Abaikan View agar tidak error saat di-import ulang
        if (strpos($table, 'view_') === 0) continue;
        
        $result = mysqli_query($conn, "SELECT * FROM $table");
        $num_fields = mysqli_num_fields($result);
        
        $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
        $sql_content .= $row2[1] . ";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $sql_content .= "INSERT INTO `$table` VALUES(";
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) { $sql_content .= '"' . $row[$j] . '"'; } else { $sql_content .= 'NULL'; }
                    if ($j < ($num_fields - 1)) { $sql_content .= ','; }
                }
                $sql_content .= ");\n";
            }
        }
        $sql_content .= "\n\n\n";
    }
    
    // Tulis data ke file secara paksa lewat PHP murni
    if (file_put_contents($backup_file, $sql_content)) {
        echo "<script>
            alert('Backup Berhasil! (Menggunakan Fallback Engine). File disimpan di: " . $backup_file . "');
            window.location.href = 'dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('Waduh, sistem gagal menulis ke folder backups. Cek izin folder kamu!');
            window.location.href = 'dashboard.php';
        </script>";
    }
}
?>