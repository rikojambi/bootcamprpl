@echo off
chcp 65001 >nul
title INSTALASI - Bootcamp Pre-UKK Fullstack Developer
echo ============================================================
echo   INSTALASI BOOTCAMP PRE-UKK FULLSTACK DEVELOPER
echo   SMKN 6 Muaro Jambi - Versi Offline Laragon
echo ============================================================
echo.

set PROJECT=%~dp0
set LARAGON=C:\laragon
set WEBROOT=%LARAGON%\www\bootcamp

echo [1/5] Memeriksa folder Laragon...
if not exist "%LARAGON%" (
    echo ERROR: Laragon tidak ditemukan di %LARAGON%.
    echo Silakan install Laragon terlebih dahulu dari laragon.org.
    pause
    exit /b 1
)
echo OK: Laragon ditemukan.

echo.
echo [2/5] Membuat junction di Laragon www...
if exist "%WEBROOT%" (
    echo INFO: %WEBROOT% sudah ada.
) else (
    mklink /J "%WEBROOT%" "%PROJECT:~0,-1%"
    if errorlevel 1 (
        echo ERROR: Gagal membuat junction. Coba jalankan sebagai Administrator.
        pause
        exit /b 1
    )
    echo OK: Junction dibuat.
)

echo.
echo [3/5] Membuat folder storage...
if not exist "%PROJECT%storage\uploads" mkdir "%PROJECT%storage\uploads"
if not exist "%PROJECT%storage\backups" mkdir "%PROJECT%storage\backups"
echo OK.

echo.
echo [4/5] Membuat database MySQL...
set MYSQL=%LARAGON%\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe
if not exist "%MYSQL%" (
    set MYSQL=%LARAGON%\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe
)
echo Lakukan import database dengan salah satu cara:
echo   a) Buka http://localhost/phpmyadmin - Import database\database.sql
echo   b) Jalankan perintah: mysql -u root ^< database\database.sql
echo.

echo.
echo [5/5] Selesai.
echo.
echo Buka Laragon, klik Start All, lalu kunjungi:
echo   http://localhost/bootcamp/
echo   http://localhost/bootcamp/ifp/
echo.
echo Akun demo:
echo   Guru  : guru  / guru123
echo   Siswa : 2023001 / siswa123
echo.
pause
