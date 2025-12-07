#!/bin/bash

# Render Build Script
# This script runs during Render deployment

echo "🚀 Starting Smart Tailoring deployment..."

# Install Composer dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p logs
mkdir -p uploads/.gitkeep

# Set permissions (Render uses ephemeral filesystem)
echo "🔒 Setting permissions..."
chmod -R 755 logs

# Download ca.pem for Aiven MySQL SSL connection
if [ ! -z "$AIVEN_CA_CERT_URL" ]; then
    echo "🔐 Downloading Aiven SSL certificate..."
    curl -o ca.pem "$AIVEN_CA_CERT_URL"
    chmod 644 ca.pem
else
    echo "⚠️  Warning: AIVEN_CA_CERT_URL not set. SSL connection may fail."
fi

# Run database migrations
echo "🗄️  Running database migrations..."
php migrate.php run || echo "⚠️  Migration failed or already up to date"

# Clear any cached config (if using OpCache)
echo "🧹 Clearing cache..."
# php artisan cache:clear # Not needed for native PHP

echo "✅ Build completed successfully!"
