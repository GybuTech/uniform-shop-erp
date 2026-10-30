@echo off
title Uniform Shop ERP Launcher
color 0A

:: Ensure working directory is always this project folder
cd /d "%~dp0"

echo ========================================================
echo         Uniform Shop ERP - Client Quick Launcher
echo ========================================================
echo.

:: 1. Check and start XAMPP MySQL silently if installed
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
    if "%ERRORLEVEL%"=="1" (
        echo [*] Starting MySQL database server...
        start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 3 /nobreak >nul
    ) else (
        echo [OK] MySQL is active and running.
    )
)

:: 2. Check and start XAMPP Apache silently if installed
if exist "C:\xampp\apache\bin\httpd.exe" (
    tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe" >NUL
    if "%ERRORLEVEL%"=="1" (
        echo [*] Starting Apache web server...
        start "" /B "C:\xampp\apache\bin\httpd.exe"
        timeout /t 2 /nobreak >nul
    ) else (
        echo [OK] Apache is active and running.
    )
)

:: 3. Launch application in default browser
echo.
echo [*] Opening Uniform Shop ERP in your browser...
start "" "http://localhost/uniform-shop-erp/public"

echo.
echo ========================================================
echo         System launched successfully!
echo ========================================================
timeout /t 3 >nul
exit
