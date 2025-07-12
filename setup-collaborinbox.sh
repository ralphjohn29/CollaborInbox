#!/bin/bash

# CollaborInbox Setup Script
# This script automates the setup process for the simplified CRM system

echo "========================================="
echo "   CollaborInbox Setup Script v1.0"
echo "========================================="
echo ""

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo "This script should not be run as root!"
   exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to generate random string
generate_random_string() {
    openssl rand -hex 32
}

# Check prerequisites
echo "Checking prerequisites..."

if ! command_exists php; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

if ! command_exists composer; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

if ! command_exists node; then
    echo "❌ Node.js is not installed. Please install Node.js 16 or higher."
    exit 1
fi

if ! command_exists npm; then
    echo "❌ NPM is not installed. Please install NPM."
    exit 1
fi

echo "✅ All prerequisites are installed."
echo ""

# Step 1: Environment Setup
echo "Step 1: Setting up environment..."

if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Created .env file from .env.example"
else
    echo "⚠️  .env file already exists. Skipping..."
fi

# Generate application key
php artisan key:generate --force
echo "✅ Generated application key"

# Step 2: Remove Tenancy Package
echo ""
echo "Step 2: Removing multi-tenancy package..."

if composer show stancl/tenancy >/dev/null 2>&1; then
    composer remove stancl/tenancy
    echo "✅ Removed stancl/tenancy package"
else
    echo "⚠️  stancl/tenancy package not found. Skipping..."
fi

# Step 3: Install Required Packages
echo ""
echo "Step 3: Installing required packages..."

# Install Laravel packages
composer require beyondcode/laravel-mailbox
composer require laravel/horizon
composer require laravel/scout
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require laravel/socialite
composer require socialiteproviders/microsoft

echo "✅ Installed all required Laravel packages"

# Install NPM packages
npm install vuedraggable@next
npm install @vueuse/core
npm install axios
npm install dayjs

echo "✅ Installed all required NPM packages"

# Step 4: Database Setup
echo ""
echo "Step 4: Setting up database..."

# Update .env with database credentials
read -p "Enter your database name [collaborinbox]: " DB_NAME
DB_NAME=${DB_NAME:-collaborinbox}

read -p "Enter your database username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Enter your database password: " DB_PASS
echo ""

# Update .env file
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

# Run migrations
php artisan migrate:fresh --force
echo "✅ Database migrated successfully"

# Step 5: Configure Services
echo ""
echo "Step 5: Configuring services..."

# Postmark configuration
read -p "Enter your Postmark Server Token (press Enter to skip): " POSTMARK_TOKEN
if [ ! -z "$POSTMARK_TOKEN" ]; then
    echo "POSTMARK_TOKEN=$POSTMARK_TOKEN" >> .env
    echo "✅ Postmark configured"
else
    echo "⚠️  Postmark configuration skipped. You'll need to configure it later."
fi

# Meilisearch configuration
read -p "Configure Meilisearch? (y/n) [y]: " CONFIGURE_MEILISEARCH
CONFIGURE_MEILISEARCH=${CONFIGURE_MEILISEARCH:-y}

if [[ $CONFIGURE_MEILISEARCH == "y" ]]; then
    read -p "Enter Meilisearch host [http://localhost:7700]: " MEILISEARCH_HOST
    MEILISEARCH_HOST=${MEILISEARCH_HOST:-http://localhost:7700}
    
    read -p "Enter Meilisearch master key (press Enter to generate): " MEILISEARCH_KEY
    if [ -z "$MEILISEARCH_KEY" ]; then
        MEILISEARCH_KEY=$(generate_random_string)
        echo "Generated Meilisearch key: $MEILISEARCH_KEY"
    fi
    
    echo "SCOUT_DRIVER=meilisearch" >> .env
    echo "MEILISEARCH_HOST=$MEILISEARCH_HOST" >> .env
    echo "MEILISEARCH_KEY=$MEILISEARCH_KEY" >> .env
    echo "✅ Meilisearch configured"
fi

# Redis configuration for queues
echo "QUEUE_CONNECTION=redis" >> .env
echo "CACHE_DRIVER=redis" >> .env
echo "SESSION_DRIVER=redis" >> .env

# Step 6: OAuth Setup (Optional)
echo ""
echo "Step 6: OAuth Configuration (Optional)..."

read -p "Configure Google OAuth? (y/n) [n]: " CONFIGURE_GOOGLE
if [[ $CONFIGURE_GOOGLE == "y" ]]; then
    read -p "Enter Google Client ID: " GOOGLE_CLIENT_ID
    read -p "Enter Google Client Secret: " GOOGLE_CLIENT_SECRET
    echo "GOOGLE_CLIENT_ID=$GOOGLE_CLIENT_ID" >> .env
    echo "GOOGLE_CLIENT_SECRET=$GOOGLE_CLIENT_SECRET" >> .env
    echo "✅ Google OAuth configured"
fi

read -p "Configure Microsoft OAuth? (y/n) [n]: " CONFIGURE_MICROSOFT
if [[ $CONFIGURE_MICROSOFT == "y" ]]; then
    read -p "Enter Microsoft Client ID: " MICROSOFT_CLIENT_ID
    read -p "Enter Microsoft Client Secret: " MICROSOFT_CLIENT_SECRET
    echo "MICROSOFT_CLIENT_ID=$MICROSOFT_CLIENT_ID" >> .env
    echo "MICROSOFT_CLIENT_SECRET=$MICROSOFT_CLIENT_SECRET" >> .env
    echo "✅ Microsoft OAuth configured"
fi

# Step 7: Publish Configuration Files
echo ""
echo "Step 7: Publishing configuration files..."

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
php artisan vendor:publish --provider="BeyondCode\Mailbox\MailboxServiceProvider"

echo "✅ Configuration files published"

# Step 8: Setup Permissions
echo ""
echo "Step 8: Setting up permissions..."

php artisan permission:create-role workspace-admin
php artisan permission:create-role agent
php artisan permission:create-role viewer

echo "✅ Roles created"

# Step 9: Build Frontend Assets
echo ""
echo "Step 9: Building frontend assets..."

npm run build
echo "✅ Frontend assets built"

# Step 10: Setup Cron Jobs
echo ""
echo "Step 10: Setting up cron jobs..."

CRON_JOB="* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "✅ Cron job added for Laravel scheduler"

# Step 11: Create Storage Links
echo ""
echo "Step 11: Creating storage links..."

php artisan storage:link
echo "✅ Storage links created"

# Step 12: Cache Configuration
echo ""
echo "Step 12: Optimizing application..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Application optimized"

# Final Steps
echo ""
echo "========================================="
echo "   Setup Complete! 🎉"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Start Redis server: redis-server"
echo "2. Start Meilisearch: meilisearch"
echo "3. Start Horizon: php artisan horizon"
echo "4. Start development server: php artisan serve"
echo "5. In another terminal: npm run dev (for development)"
echo ""
echo "Default URLs:"
echo "- Application: http://localhost:8000"
echo "- Horizon Dashboard: http://localhost:8000/horizon"
echo "- Signup Page: http://localhost:8000/signup"
echo ""
echo "For production deployment:"
echo "- Configure your web server (Nginx/Apache)"
echo "- Set up SSL certificates"
echo "- Configure process managers for Horizon"
echo "- Set up proper backups"
echo ""
echo "Documentation: https://github.com/yourusername/collaborinbox"
echo ""
