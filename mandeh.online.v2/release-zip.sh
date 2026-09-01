#!/bin/bash

# Mandeh.Online v2 - Packaging Script
# Standardized Build & Zip Workflow

set -e

# Change directory to the script's directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "=== Starting Build and Packaging Process ==="

# 1. Frontend Asset Build
echo "Building frontend assets using Docker Node container..."
docker run --rm -v "$(pwd):/var/www" -w /var/www node:20-alpine sh -c "npm install && npm run build"

# 2. Backend Dependency Installation (Composer)
echo "Installing backend dependencies for production..."
docker compose -f ../compose.yaml exec -T php-fpm composer install --no-dev --optimize-autoloader --working-dir=mandeh.online.v2

# 3. Handle Environment Configuration (.env)
echo "Preparing production .env from .env.domain..."
if [ -f .env ]; then
    echo "Backing up local .env to .env.local.bak"
    mv .env .env.local.bak
fi

# Copy production env to .env for packaging
cp .env.domain .env

# 4. Packaging into Tarball
echo "Creating Tarball package..."
TAR_NAME="mandeh-online-v2-release.tar.gz"

# Remove old tar.gz if it exists
if [ -f "$TAR_NAME" ]; then
    rm "$TAR_NAME"
fi

# Tar the directory excluding developer and build files
tar --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='*.tar.gz' \
    --exclude='*.zip' \
    --exclude='release-zip.sh' \
    --exclude='.env.local.bak' \
    -czf "../$TAR_NAME" .

mv "../$TAR_NAME" .

# 5. Clean up and restore local env
echo "Cleaning up temporary files..."
rm .env

if [ -f .env.local.bak ]; then
    echo "Restoring local .env"
    mv .env.local.bak .env
fi

echo "=========================================="
echo "Build and Packaging completed successfully!"
echo "Artifact created: mandeh.online.v2/$TAR_NAME"
echo "=========================================="
