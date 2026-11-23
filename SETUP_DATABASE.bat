@echo off
setlocal enabledelayedexpansion
title Tshaprint POS - 1-Click Database Setup
color 0B

echo ====================================================================
echo             TSHAPRINT POS - AUTOMATED DATABASE SETUP
echo ====================================================================
echo.

cd /d "%~dp0"

:: 1. Locate MySQL binary
set "MYSQL_BIN="
if exist "C:\xampp\mysql\bin\mysql.exe" (
    set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
) else if exist "D:\xampp\mysql\bin\mysql.exe" (
    set "MYSQL_BIN=D:\xampp\mysql\bin\mysql.exe"
) else (
    for /f "tokens=*" %%i in ('where mysql 2^>nul') do (
        set "MYSQL_BIN=%%i"
    )
)

if "%MYSQL_BIN%"=="" (
    color 0C
    echo [ERROR] MySQL binary not found in C:\xampp\mysql\bin.
    echo Please make sure XAMPP is installed.
    echo.
    pause
    exit /b 1
)

:: 2. Ensure MySQL is running
echo [1/3] Ensuring MySQL database service is running...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if "%ERRORLEVEL%"=="1" (
    echo       Starting MySQL service...
    if exist "C:\xampp\mysql_start.bat" (
        call "C:\xampp\mysql_start.bat" >nul 2>&1
    ) else if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start /b "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone >nul 2>&1
    )
    timeout /t 3 /nobreak >nul
)

:: 3. Create database if it does not exist
echo [2/3] Creating database 'uniform_shop_erp' if missing...
"!MYSQL_BIN!" -u root -e "CREATE DATABASE IF NOT EXISTS uniform_shop_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1
if "%ERRORLEVEL%"=="0" (
    echo       Database 'uniform_shop_erp' is ready!
) else (
    echo       Could not connect to MySQL. Please ensure MySQL is running in XAMPP.
)

:: 4. Import SQL dump if present
set "SQL_FILE="
if exist "uniform_shop_erp.sql" (
    set "SQL_FILE=uniform_shop_erp.sql"
) else if exist "..\uniform_shop_erp.sql" (
    set "SQL_FILE=..\uniform_shop_erp.sql"
) else if exist "uniform_shop_erp (3).sql" (
    set "SQL_FILE=uniform_shop_erp (3).sql"
) else if exist "..\uniform_shop_erp (3).sql" (
    set "SQL_FILE=..\uniform_shop_erp (3).sql"
) else (
    for %%f in (*.sql) do set "SQL_FILE=%%f"
    if not defined SQL_FILE (
        for %%f in (..\*.sql) do set "SQL_FILE=%%f"
    )
)

:: Never import over a shop that already has data: only into an empty database.
set "TABLE_COUNT=0"
for /f "skip=1 tokens=*" %%c in ('"!MYSQL_BIN!" -u root -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='uniform_shop_erp';" 2^>nul') do set "TABLE_COUNT=%%c"
if not "!TABLE_COUNT!"=="0" (
    echo [3/3] Database already has !TABLE_COUNT! tables: this shop is set up. Nothing imported.
    echo       Start the POS with RUN_POS.bat; it applies any updates itself.
    set "SQL_FILE="
    goto :done
)

if not "%SQL_FILE%"=="" (
    echo [3/3] Importing %SQL_FILE% into uniform_shop_erp ...
    "!MYSQL_BIN!" -u root uniform_shop_erp < "%SQL_FILE%"
    if errorlevel 1 (
        color 0C
        echo       [ERROR] The import FAILED. The database may be incomplete; call support.
    ) else (
        echo       Database imported successfully!
    )
) else (
    echo [3/3] No .sql file found in folder. Running migrations instead...
    set "PHP_BIN="
    if exist "C:\xampp\php\windowsxamppPhp\php.exe" set "PHP_BIN=C:\xampp\php\windowsxamppPhp\php.exe"
    if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
    if defined PHP_BIN (
        "!PHP_BIN!" artisan migrate --force
    )
)

:done
echo.
echo ====================================================================
echo  SETUP COMPLETE!
echo  You can now double-click RUN_POS.bat to start the system anytime!
echo ====================================================================
echo.
pause

