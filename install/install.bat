@echo off
setlocal EnableDelayedExpansion

:: ===== DEFAULT CONFIG =====
set "DEFAULT_XAMPP=C:\xampp"
set "DEFAULT_DB_HOST=localhost"
set "DEFAULT_DB_USER=root"
set "DEFAULT_DB_NAME=salespage"

:: ===== ASK XAMPP DIR =====
set "XAMPP_DIR=%DEFAULT_XAMPP%"
set /p USER_INPUT=Enter XAMPP directory [%XAMPP_DIR%]:
if not "!USER_INPUT!"=="" set "XAMPP_DIR=!USER_INPUT!"

:: ===== NORMALIZE PROJECT DIR =====
set "PROJECT_DIR=%~dp0\.."
for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

set "FULL_SQL=%~dp0full_schema.sql"

:: ===== LOCATE MYSQL =====
set "MYSQL_BIN=%XAMPP_DIR%\mysql\bin\mysql.exe"
if not exist "!MYSQL_BIN!" set "MYSQL_BIN=%XAMPP_DIR%\bin\mysql\bin\mysql.exe"

if not exist "!MYSQL_BIN!" (
    where mysql >nul 2>nul
    if !errorlevel! == 0 (
        for /f "usebackq tokens=*" %%a in (`where mysql`) do (
            set "MYSQL_BIN=%%a"
            goto found_mysql
        )
    )
)

:found_mysql
if not exist "!MYSQL_BIN!" (
    echo [ERROR] mysql.exe not found!
    pause
    exit /b 1
)

:: ===== CHECK MYSQL RUNNING =====
echo.
echo Checking MySQL process...

tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL

if !errorlevel! == 0 (
    echo [OK] MySQL is running.
) else (
    echo [ERROR] MySQL is NOT running.
    echo Please start MySQL in XAMPP Control Panel then press ENTER.
    pause >nul
)

:: ===== DB INPUT =====
set /p DB_HOST=Enter DB host [%DEFAULT_DB_HOST%]:
if "!DB_HOST!"=="" set "DB_HOST=%DEFAULT_DB_HOST%"

set /p DB_USER=Enter DB user [%DEFAULT_DB_USER%]:
if "!DB_USER!"=="" set "DB_USER=%DEFAULT_DB_USER%"

set /p DB_PASS=Enter DB password (leave blank for none):

set /p DB_NAME=Enter DB name [%DEFAULT_DB_NAME%]:
if "!DB_NAME!"=="" set "DB_NAME=%DEFAULT_DB_NAME%"

:: ===== CONFIRM =====
echo.
echo Using:
echo  PROJECT_DIR = !PROJECT_DIR!
echo  MYSQL_BIN   = !MYSQL_BIN!
echo  DB_HOST     = !DB_HOST!
echo  DB_USER     = !DB_USER!
echo  DB_NAME     = !DB_NAME!
echo.

set /p CONFIRM=Proceed with installation? (Y/n):
if /I "!CONFIRM!"=="n" (
    echo Aborted.
    exit /b 0
)

:: ===== CREATE DATABASE =====
echo Creating database !DB_NAME!...

if "!DB_PASS!"=="" (
    "!MYSQL_BIN!" -h "!DB_HOST!" -u "!DB_USER!" -e "CREATE DATABASE IF NOT EXISTS !DB_NAME! DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) else (
    "!MYSQL_BIN!" -h "!DB_HOST!" -u "!DB_USER!" -p"!DB_PASS!" -e "CREATE DATABASE IF NOT EXISTS !DB_NAME! DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

if !errorlevel! neq 0 (
    echo [ERROR] Failed to create database!
    pause
    exit /b 1
)

:: ===== IMPORT FULL SQL =====
if not exist "!FULL_SQL!" (
    echo [ERROR] full_schema.sql not found!
    pause
    exit /b 1
)

echo Importing FULL schema...

if "!DB_PASS!"=="" (
    "!MYSQL_BIN!" -h "!DB_HOST!" -u "!DB_USER!" "!DB_NAME!" < "!FULL_SQL!"
) else (
    "!MYSQL_BIN!" -h "!DB_HOST!" -u "!DB_USER!" -p"!DB_PASS!" "!DB_NAME!" < "!FULL_SQL!"
)

if !errorlevel! neq 0 (
    echo [ERROR] Failed to import full_schema.sql
    pause
    exit /b 1
)

:: ===== DONE =====
echo.
echo [SUCCESS] Installation complete.
echo Open: http://localhost/sell-shop-SPU/
pause