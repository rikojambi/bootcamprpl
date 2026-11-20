@echo off
chcp 65001 >nul
title BACKUP DATABASE - Bootcamp Pre-UKK
echo ============================================================
echo   BACKUP DATABASE bootcamp_fullstack
echo ============================================================
echo.

set BACKUPDIR=%~dp0storage\backups
set MYSQLDUMP=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe
set STAMP=%DATE:~-4,4%%DATE:~-10,2%%DATE:~-7,2%_%TIME:~0,2%%TIME:~3,2%%TIME:~6,2%
set STAMP=%STAMP: =0%
set FILE=%BACKUPDIR%\backup_%STAMP%.sql

if not exist "%BACKUPDIR%" mkdir "%BACKUPDIR%"

echo Menyimpan ke: %FILE%
"%MYSQLDUMP%" -u root bootcamp_fullstack > "%FILE%"
if errorlevel 1 (
    echo ERROR: Backup gagal. Pastikan MySQL Laragon berjalan.
) else (
    echo OK: Backup berhasil dibuat.
    dir "%FILE%"
)
echo.
pause
