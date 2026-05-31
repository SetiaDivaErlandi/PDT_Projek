<?php
session_start();
// Hancurkan semua sesi login aktif
session_unset();
session_destroy();

// Kembalikan ke halaman login utama
header("Location: index.php");
exit;
?>