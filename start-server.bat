@echo off
chcp 65001 >nul
title START SERVER - Bootcamp Pre-UKK
echo Menjalankan server lokal untuk http://localhost/bootcamp/
echo.

set PHP=C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe
set ROOT=C:\laragon\www

if not exist "%PHP%" (
    echo ERROR: PHP tidak ditemukan. Sesuaikan path PHP di file ini.
    pause
    exit /b 1
)

echo Pastikan MySQL Laragon sudah berjalan (port 3306).
echo Membuka server PHP di port 8080...
echo.
echo URL: http://localhost:8080/bootcamp/
echo URL IFP: http://localhost:8080/bootcamp/ifp/
echo.
"%PHP%" -S 127.0.0.1:8080 -t "%ROOT%"
pause
