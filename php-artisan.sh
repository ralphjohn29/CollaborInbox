#!/bin/bash
# PHP Artisan Wrapper Script for Git Bash

# Check common PHP installation directories
PHP_EXECUTABLE=""

# Try to use system PHP first
if command -v php &> /dev/null; then
    PHP_EXECUTABLE=$(command -v php)
# Try common Windows PHP installation paths (converted to Git Bash paths)
elif [ -f "/c/php/php.exe" ]; then
    PHP_EXECUTABLE="/c/php/php.exe"
elif [ -f "/c/xampp/php/php.exe" ]; then
    PHP_EXECUTABLE="/c/xampp/php/php.exe"
elif [ -f "/c/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe" ]; then
    PHP_EXECUTABLE="/c/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe"
elif [ -f "/c/Program Files/php/php.exe" ]; then
    PHP_EXECUTABLE="/c/Program Files/php/php.exe"
fi

if [ -z "$PHP_EXECUTABLE" ]; then
    echo "PHP executable not found in common locations."
    echo "Please install PHP or edit this script with the correct PHP path."
    exit 1
fi

echo "Using PHP: $PHP_EXECUTABLE"
"$PHP_EXECUTABLE" artisan "$@" 