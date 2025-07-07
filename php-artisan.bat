@echo off
REM PHP Artisan Wrapper Script

REM Check common PHP installation directories
SET PHP_EXECUTABLE=

REM Try common PHP installation paths
IF EXIST "C:\php\php.exe" (
    SET PHP_EXECUTABLE=C:\php\php.exe
) ELSE IF EXIST "C:\xampp\php\php.exe" (
    SET PHP_EXECUTABLE=C:\xampp\php\php.exe
) ELSE IF EXIST "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
    SET PHP_EXECUTABLE=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
) ELSE IF EXIST "C:\Program Files\php\php.exe" (
    SET PHP_EXECUTABLE="C:\Program Files\php\php.exe"
)

IF "%PHP_EXECUTABLE%"=="" (
    ECHO PHP executable not found in common locations.
    ECHO Please install PHP or edit this script with the correct PHP path.
    EXIT /B 1
)

ECHO Using PHP: %PHP_EXECUTABLE%
%PHP_EXECUTABLE% artisan %* 