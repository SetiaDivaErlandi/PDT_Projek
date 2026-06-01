@echo off
:: Berpindah ke folder aktif PHP XAMPP secara absolut
cd /d "C:\xampp\php"

:: Mengeksekusi file php dengan jalur proyek yang diapit tanda petik dua
php.exe -f "C:\Users\Hype AMD\OneDrive\Documents\PDT_Projek\cron_backup.php"
exit