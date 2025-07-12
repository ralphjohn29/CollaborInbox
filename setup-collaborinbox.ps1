# CollaborInbox Setup Script for Windows
# This script automates the setup process for the simplified CRM system

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "   CollaborInbox Setup Script v1.0" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Function to check if command exists
function Test-Command {
    param($Command)
    try {
        Get-Command $Command -ErrorAction Stop | Out-Null
        return $true
    } catch {
        return $false
    }
}

# Function to generate random string
function Get-RandomString {
    -join ((65..90) + (97..122) + (48..57) | Get-Random -Count 32 | ForEach-Object {[char]$_})
}

# Check prerequisites
Write-Host "Checking prerequisites..." -ForegroundColor Yellow

if (!(Test-Command "php")) {
    Write-Host "❌ PHP is not installed. Please install PHP 8.1 or higher." -ForegroundColor Red
    exit 1
}

if (!(Test-Command "composer")) {
    Write-Host "❌ Composer is not installed. Please install Composer." -ForegroundColor Red
    exit 1
}

if (!(Test-Command "node")) {
    Write-Host "❌ Node.js is not installed. Please install Node.js 16 or higher." -ForegroundColor Red
    exit 1
}

if (!(Test-Command "npm")) {
    Write-Host "❌ NPM is not installed. Please install NPM." -ForegroundColor Red
    exit 1
}

Write-Host "✅ All prerequisites are installed." -ForegroundColor Green
Write-Host ""

# Step 1: Environment Setup
Write-Host "Step 1: Setting up environment..." -ForegroundColor Yellow

if (!(Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✅ Created .env file from .env.example" -ForegroundColor Green
} else {
    Write-Host "⚠️  .env file already exists. Skipping..." -ForegroundColor Yellow
}

# Generate application key
php artisan key:generate --force
Write-Host "✅ Generated application key" -ForegroundColor Green

# Step 2: Remove Tenancy Package
Write-Host ""
Write-Host "Step 2: Removing multi-tenancy package..." -ForegroundColor Yellow

$tenancyInstalled = composer show stancl/tenancy 2>$null
if ($LASTEXITCODE -eq 0) {
    composer remove stancl/tenancy
    Write-Host "✅ Removed stancl/tenancy package" -ForegroundColor Green
} else {
    Write-Host "⚠️  stancl/tenancy package not found. Skipping..." -ForegroundColor Yellow
}

# Step 3: Install Required Packages
Write-Host ""
Write-Host "Step 3: Installing required packages..." -ForegroundColor Yellow

# Install Laravel packages
composer require beyondcode/laravel-mailbox
composer require laravel/horizon
composer require laravel/scout
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require laravel/socialite
composer require socialiteproviders/microsoft

Write-Host "✅ Installed all required Laravel packages" -ForegroundColor Green

# Install NPM packages
npm install vuedraggable@next
npm install @vueuse/core
npm install axios
npm install dayjs

Write-Host "✅ Installed all required NPM packages" -ForegroundColor Green

# Step 4: Database Setup
Write-Host ""
Write-Host "Step 4: Setting up database..." -ForegroundColor Yellow

# Update .env with database credentials
$DB_NAME = Read-Host "Enter your database name [collaborinbox]"
if ([string]::IsNullOrWhiteSpace($DB_NAME)) { $DB_NAME = "collaborinbox" }

$DB_USER = Read-Host "Enter your database username [root]"
if ([string]::IsNullOrWhiteSpace($DB_USER)) { $DB_USER = "root" }

$DB_PASS = Read-Host "Enter your database password" -AsSecureString
$DB_PASS_TEXT = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($DB_PASS))

# Update .env file
$envContent = Get-Content .env
$envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$DB_NAME"
$envContent = $envContent -replace "DB_USERNAME=.*", "DB_USERNAME=$DB_USER"
$envContent = $envContent -replace "DB_PASSWORD=.*", "DB_PASSWORD=$DB_PASS_TEXT"
Set-Content .env $envContent

# Run migrations
php artisan migrate:fresh --force
Write-Host "✅ Database migrated successfully" -ForegroundColor Green

# Step 5: Configure Services
Write-Host ""
Write-Host "Step 5: Configuring services..." -ForegroundColor Yellow

# Postmark configuration
$POSTMARK_TOKEN = Read-Host "Enter your Postmark Server Token (press Enter to skip)"
if (![string]::IsNullOrWhiteSpace($POSTMARK_TOKEN)) {
    Add-Content .env "POSTMARK_TOKEN=$POSTMARK_TOKEN"
    Write-Host "✅ Postmark configured" -ForegroundColor Green
} else {
    Write-Host "⚠️  Postmark configuration skipped. You'll need to configure it later." -ForegroundColor Yellow
}

# Meilisearch configuration
$CONFIGURE_MEILISEARCH = Read-Host "Configure Meilisearch? (y/n) [y]"
if ([string]::IsNullOrWhiteSpace($CONFIGURE_MEILISEARCH)) { $CONFIGURE_MEILISEARCH = "y" }

if ($CONFIGURE_MEILISEARCH -eq "y") {
    $MEILISEARCH_HOST = Read-Host "Enter Meilisearch host [http://localhost:7700]"
    if ([string]::IsNullOrWhiteSpace($MEILISEARCH_HOST)) { $MEILISEARCH_HOST = "http://localhost:7700" }
    
    $MEILISEARCH_KEY = Read-Host "Enter Meilisearch master key (press Enter to generate)"
    if ([string]::IsNullOrWhiteSpace($MEILISEARCH_KEY)) {
        $MEILISEARCH_KEY = Get-RandomString
        Write-Host "Generated Meilisearch key: $MEILISEARCH_KEY" -ForegroundColor Cyan
    }
    
    Add-Content .env "SCOUT_DRIVER=meilisearch"
    Add-Content .env "MEILISEARCH_HOST=$MEILISEARCH_HOST"
    Add-Content .env "MEILISEARCH_KEY=$MEILISEARCH_KEY"
    Write-Host "✅ Meilisearch configured" -ForegroundColor Green
}

# Redis configuration for queues
Add-Content .env "QUEUE_CONNECTION=redis"
Add-Content .env "CACHE_DRIVER=redis"
Add-Content .env "SESSION_DRIVER=redis"

# Step 6: OAuth Setup (Optional)
Write-Host ""
Write-Host "Step 6: OAuth Configuration (Optional)..." -ForegroundColor Yellow

$CONFIGURE_GOOGLE = Read-Host "Configure Google OAuth? (y/n) [n]"
if ($CONFIGURE_GOOGLE -eq "y") {
    $GOOGLE_CLIENT_ID = Read-Host "Enter Google Client ID"
    $GOOGLE_CLIENT_SECRET = Read-Host "Enter Google Client Secret"
    Add-Content .env "GOOGLE_CLIENT_ID=$GOOGLE_CLIENT_ID"
    Add-Content .env "GOOGLE_CLIENT_SECRET=$GOOGLE_CLIENT_SECRET"
    Write-Host "✅ Google OAuth configured" -ForegroundColor Green
}

$CONFIGURE_MICROSOFT = Read-Host "Configure Microsoft OAuth? (y/n) [n]"
if ($CONFIGURE_MICROSOFT -eq "y") {
    $MICROSOFT_CLIENT_ID = Read-Host "Enter Microsoft Client ID"
    $MICROSOFT_CLIENT_SECRET = Read-Host "Enter Microsoft Client Secret"
    Add-Content .env "MICROSOFT_CLIENT_ID=$MICROSOFT_CLIENT_ID"
    Add-Content .env "MICROSOFT_CLIENT_SECRET=$MICROSOFT_CLIENT_SECRET"
    Write-Host "✅ Microsoft OAuth configured" -ForegroundColor Green
}

# Step 7: Publish Configuration Files
Write-Host ""
Write-Host "Step 7: Publishing configuration files..." -ForegroundColor Yellow

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
php artisan vendor:publish --provider="BeyondCode\Mailbox\MailboxServiceProvider"

Write-Host "✅ Configuration files published" -ForegroundColor Green

# Step 8: Setup Permissions
Write-Host ""
Write-Host "Step 8: Setting up permissions..." -ForegroundColor Yellow

php artisan permission:create-role workspace-admin
php artisan permission:create-role agent
php artisan permission:create-role viewer

Write-Host "✅ Roles created" -ForegroundColor Green

# Step 9: Build Frontend Assets
Write-Host ""
Write-Host "Step 9: Building frontend assets..." -ForegroundColor Yellow

npm run build
Write-Host "✅ Frontend assets built" -ForegroundColor Green

# Step 10: Create Storage Links
Write-Host ""
Write-Host "Step 10: Creating storage links..." -ForegroundColor Yellow

php artisan storage:link
Write-Host "✅ Storage links created" -ForegroundColor Green

# Step 11: Cache Configuration
Write-Host ""
Write-Host "Step 11: Optimizing application..." -ForegroundColor Yellow

php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "✅ Application optimized" -ForegroundColor Green

# Step 12: Create Windows Task for Scheduler
Write-Host ""
Write-Host "Step 12: Setting up task scheduler..." -ForegroundColor Yellow

$taskName = "CollaborInbox-Scheduler"
$action = New-ScheduledTaskAction -Execute "php" -Argument "artisan schedule:run" -WorkingDirectory $PWD
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
$principal = New-ScheduledTaskPrincipal -UserId $env:USERNAME -LogonType Interactive

try {
    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Force
    Write-Host "✅ Task scheduler configured" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not create scheduled task. Please run scheduler manually." -ForegroundColor Yellow
}

# Final Steps
Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "   Setup Complete! 🎉" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Start Redis server (if using Windows Subsystem for Linux or Docker)"
Write-Host "2. Start Meilisearch: meilisearch.exe"
Write-Host "3. Start Horizon: php artisan horizon"
Write-Host "4. Start development server: php artisan serve"
Write-Host "5. In another terminal: npm run dev (for development)"
Write-Host ""
Write-Host "Default URLs:" -ForegroundColor Cyan
Write-Host "- Application: http://localhost:8000"
Write-Host "- Horizon Dashboard: http://localhost:8000/horizon"
Write-Host "- Signup Page: http://localhost:8000/signup"
Write-Host ""
Write-Host "For production deployment:" -ForegroundColor Yellow
Write-Host "- Configure IIS or use Laravel Valet for Windows"
Write-Host "- Set up SSL certificates"
Write-Host "- Configure process managers for Horizon"
Write-Host "- Set up proper backups"
Write-Host ""
Write-Host "Documentation: https://github.com/yourusername/collaborinbox" -ForegroundColor Cyan
Write-Host ""
