# Laravel Sail PowerShell wrapper for Windows
$ErrorActionPreference = "Stop"

# Get the command and arguments
$command = $args[0]
$remainingArgs = $args[1..$args.Length]

# Check if Docker is running
docker info > $null 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Docker is not running. Please start Docker Desktop." -ForegroundColor Red
    exit 1
}

# Execute the sail command
& "$PSScriptRoot\vendor\bin\sail.ps1" @args
