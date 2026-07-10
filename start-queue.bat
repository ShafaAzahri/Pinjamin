@echo off
echo ==============================================
echo Menjalankan Laravel Queue Worker...
echo ==============================================
php artisan queue:listen
pause
