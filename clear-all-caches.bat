@echo off
REM This batch file clears all Laravel caches

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

ECHO Clearing all Laravel caches...

ECHO Clearing configuration cache...
%PHP_EXECUTABLE% artisan config:clear

ECHO Clearing application cache...
%PHP_EXECUTABLE% artisan cache:clear

ECHO Clearing route cache...
%PHP_EXECUTABLE% artisan route:clear

ECHO Clearing view cache...
%PHP_EXECUTABLE% artisan view:clear

ECHO Clearing compiled views...
%PHP_EXECUTABLE% artisan view:clear

ECHO All caches cleared successfully.
ECHO Run 'serve.bat' to start the server with a clean state.

PAUSE 