@echo off
setlocal enabledelayedexpansion
title Tshaprint Uniform POS Launcher
color 0A

echo ====================================================================
echo                   TSHAPRINT UNIFORM SHOP POS
echo ====================================================================
echo.

:: 1. Navigate to the exact folder where this batch file is located
cd /d "%~dp0"
echo [1/4] Working Directory: %CD%

:: 2. Auto-detect PHP executable
echo [2/4] Detecting PHP Engine...
set "PHP_BIN="

if exist "C:\xampp\php\windowsxamppPhp\php.exe" (
    set "PHP_BIN=C:\xampp\php\windowsxamppPhp\php.exe"
    set "EXT_DIR=C:\xampp\php\ext"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
    set "EXT_DIR=C:\xampp\php\ext"
) else if exist "D:\xampp\php\windowsxamppPhp\php.exe" (
    set "PHP_BIN=D:\xampp\php\windowsxamppPhp\php.exe"
    set "EXT_DIR=D:\xampp\php\ext"
) else if exist "D:\xampp\php\php.exe" (
    set "PHP_BIN=D:\xampp\php\php.exe"
    set "EXT_DIR=D:\xampp\php\ext"
) else (
    for /f "tokens=*" %%i in ('where php 2^>nul') do (
        set "PHP_BIN=%%i"
    )
)

if "%PHP_BIN%"=="" (
    color 0C
    echo.
    echo [ERROR] Could not find php.exe in C:\xampp\php or system PATH.
    echo Please make sure XAMPP is installed on this computer.
    echo.
    pause
    exit /b 1
)

echo       Found PHP at: !PHP_BIN!

:: 3. Check XAMPP MySQL Engine
echo [3/4] Checking Database Engine...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if "%ERRORLEVEL%"=="1" (
    echo       Starting MySQL in background...
    if exist "C:\xampp\mysql_start.bat" (
        start /b "" "C:\xampp\mysql_start.bat" >nul 2>&1
    ) else if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start /b "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone >nul 2>&1
    ) else if exist "D:\xampp\mysql\bin\mysqld.exe" (
        start /b "" "D:\xampp\mysql\bin\mysqld.exe" --defaults-file="D:\xampp\mysql\bin\my.ini" --standalone >nul 2>&1
    )
    timeout /t 3 /nobreak >nul
) else (
    echo       MySQL is already running.
)

:: 4. Free port 8000 if blocked by previous crashed/lingering processes
echo [4/5] Freeing port 8000 from old processes...
for /f "tokens=5" %%p in ('netstat -aon 2^>nul ^| findstr ":8000 "') do (
    if not "%%p"=="0" (
        taskkill /F /PID %%p >nul 2>&1
    )
)
timeout /t 1 /nobreak >nul

:: Back up the database before applying any updates (only when updates are waiting).
:: Backups are kept in storage\backups (the newest 20).
if defined EXT_DIR (
    set "PHP_RUN="!PHP_BIN!" -d extension_dir="!EXT_DIR!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl"
) else (
    set "PHP_RUN="!PHP_BIN!""
)
echo       Checking for database updates...
!PHP_RUN! artisan pos:backup --if-pending
if errorlevel 1 (
    color 0E
    echo.
    echo [WARNING] The database could not be backed up, so updates were NOT applied.
    echo           Call support before trading. Press any key to start the POS anyway,
    echo           or close this window to stop.
    pause >nul
    color 0A
) else (
    rem Apply any pending database migrations (new tables/columns shipped with updates)
    !PHP_RUN! artisan migrate --force
)

:: Health check: lists anything that would stop trading or print wrong details on receipts
!PHP_RUN! artisan pos:check
echo.

:: 5. Launch POS Application
echo [5/5] Launching POS Server on http://127.0.0.1:8000 ...
echo.
echo ====================================================================
echo  POS TERMINAL IS READY!
echo  Opening browser to: http://127.0.0.1:8000/login
echo.
echo  KEEP THIS WINDOW OPEN while using the POS.
echo  To shut down the POS at the end of the day, simply close this window.
echo ====================================================================
echo.

:: Background cloud sync: uploads this shop's sales to HQ every minute when online.
:: Does nothing until SYNC_HQ_URL and SYNC_TOKEN are set in .env. Runs minimized.
if defined EXT_DIR (
    start "POS Cloud Sync (keep open)" /min "!PHP_BIN!" -d extension_dir="!EXT_DIR!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan schedule:work
) else (
    start "POS Cloud Sync (keep open)" /min "!PHP_BIN!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan schedule:work
)

:: Open browser after a brief 2-second delay so the PHP server is fully listening
start "" cmd /c "timeout /t 2 /nobreak >nul & start http://127.0.0.1:8000/login"

:: Run server in foreground so window stays open and serves requests
if defined EXT_DIR (
    "!PHP_BIN!" -d extension_dir="!EXT_DIR!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan serve --host=127.0.0.1 --port=8000
) else (
    "!PHP_BIN!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan serve --host=127.0.0.1 --port=8000
)

if "%ERRORLEVEL%" NEQ "0" (
    echo.
    echo [WARNING] Port 8000 could not be bound. Trying alternative port 8080...
    start "" cmd /c "timeout /t 2 /nobreak >nul & start http://127.0.0.1:8080/login"
    if defined EXT_DIR (
        "!PHP_BIN!" -d extension_dir="!EXT_DIR!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan serve --host=127.0.0.1 --port=8080
    ) else (
        "!PHP_BIN!" -d extension=mbstring -d extension=pdo_mysql -d extension=fileinfo -d extension=openssl -d extension=curl artisan serve --host=127.0.0.1 --port=8080
    )
)

if "%ERRORLEVEL%" NEQ "0" (
    color 0C
    echo.
    echo [NOTICE] Server stopped.
    pause
)

