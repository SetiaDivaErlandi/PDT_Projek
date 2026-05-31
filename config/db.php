<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "labtrack";

// Membuat koneksi ke MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi sistem
if (!$conn) {
    die("Koneksi ke database LabTrack gagal: " . mysqli_connect_error());
}
?>