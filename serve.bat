@echo off
REM This batch file sets up and serves the Laravel application

REM Find PHP executable
SET PHP_EXECUTABLE=

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

REM Clear configuration cache
ECHO Clearing configuration cache...
%PHP_EXECUTABLE% artisan config:clear

REM Update .env file if needed
REM You can add commands here to update the .env file if necessary

REM Start the server with the correct host and port
ECHO Starting Laravel development server...
%PHP_EXECUTABLE% artisan serve --host=collaborinbox.test --port=8000

PAUSE 