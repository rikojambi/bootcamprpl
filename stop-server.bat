@echo off
chcp 65001 >nul
title STOP SERVER - Bootcamp Pre-UKK
echo Menghentikan server PHP port 8080 dan MySQL Laragon...
echo.

echo [1/2] Menghentikan PHP built-in server (port 8080)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8080" ^| findstr "LISTENING"') do (
    echo   Menghentikan PID %%a
    taskkill /F /PID %%a >nul 2>&1
)

echo [2/2] Menghentikan mysqld.exe Laragon...
taskkill /F /IM mysqld.exe >nul 2>&1

echo.
echo Selesai. Jalankan start-server.bat untuk menyalakan kembali.
pause
