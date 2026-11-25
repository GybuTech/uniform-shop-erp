@echo off
title GybuTech POS - Client Store Handover Reset
color 0b

echo ====================================================================
echo             GYBUTECH POS - CLIENT STORE HANDOVER RESET
echo ====================================================================
echo.
echo  This tool safely prepares your system for a brand new client store.
echo  It clears out all test transactions, receipts, cashier shifts,
echo  purchases, returns, expenses, and audit logs.
echo.
echo  Product Catalog, Categories, Prices, Store Settings, and Users
echo  are 100%% PRESERVED and protected.
echo.
echo ====================================================================
echo  CHOOSE HANDOVER MODE:
echo ====================================================================
echo  [1] Clean test data and KEEP product stock quantities as-is
echo  [2] Clean test data and ZERO ALL stock quantities (blank stocktake)
echo  [3] Cancel and exit
echo ====================================================================
echo.

set /p choice="Enter your choice (1, 2, or 3): "

if "%choice%"=="1" goto KEEP_STOCK
if "%choice%"=="2" goto ZERO_STOCK
if "%choice%"=="3" goto CANCEL
goto INVALID

:KEEP_STOCK
echo.
echo  Wiping transactional test data while PRESERVING product stock quantities...
php artisan pos:handover --force --keep-stock
goto FINISH

:ZERO_STOCK
echo.
echo  Wiping transactional test data and RESETTING inventory quantities to 0...
php artisan pos:handover --force
goto FINISH

:FINISH
echo.
echo  Clearing and optimizing application caches...
php artisan optimize:clear
php artisan view:clear
php artisan cache:clear

echo.
echo ====================================================================
echo        CLIENT INSTALLATION RESET COMPLETED SUCCESSFULLY!
echo ====================================================================
echo  The system is now completely clean and ready for your client!
echo ====================================================================
echo.
pause
exit

:CANCEL
echo.
echo  Operation cancelled. No changes were made.
pause
exit

:INVALID
echo.
echo  Invalid choice. Please run the script again.
pause
exit
