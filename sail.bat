@echo off

REM Check if Docker is running
docker info > NUL 2>&1
if %ERRORLEVEL% neq 0 (
    echo Docker is not running. Please start Docker Desktop.
    exit /b 1
)

REM Run the sail command
docker compose exec "laravel.test" bash vendor/bin/sail %*
