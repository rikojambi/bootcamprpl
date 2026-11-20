@echo off
chcp 65001 >nul
title RESTORE DATABASE - Bootcamp Pre-UKK
echo ============================================================
echo   RESTORE DATABASE bootcamp_fullstack
echo ============================================================
echo.

set MYSQL=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe
set BACKUPDIR=%~dp0..\storage\backups

echo Daftar file backup di %BACKUPDIR%:
dir /b "%BACKUPDIR%\*.sql" 2>nul
echo.

set /p FILE=Nama file backup (contoh backup_20261121_070000.sql):
if "%FILE%"=="" (
    echo ERROR: Nama file tidak boleh kosong.
    pause
    exit /b 1
)

if not exist "%BACKUPDIR%\%FILE%" (
    echo ERROR: File tidak ditemukan.
    pause
    exit /b 1
)

echo.
echo PERINGATAN: Restore akan menimpa seluruh isi database bootcamp_fullstack.
pause

echo Membuat database jika belum ada...
"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS bootcamp_fullstack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Memulihkan dari %FILE%...
"%MYSQL%" -u root bootcamp_fullstack < "%BACKUPDIR%\%FILE%"
if errorlevel 1 (
    echo ERROR: Restore gagal.
) else (
    echo OK: Restore berhasil. Silakan buka http://localhost/bootcamp/
)
echo.
pause
