#!/bin/bash

# Sachs Admin Deployment Script for Hetzner
# This script deploys the Laravel application to a Hetzner server

set -e

# Configuration
APP_NAME="sachs-admin"
DEPLOY_USER="deploy"
DEPLOY_PATH="/var/www/$APP_NAME"
BACKUP_PATH="/var/backups/$APP_NAME"
LOG_FILE="/var/log/$APP_NAME/deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root"
fi

# Create log directory
sudo mkdir -p /var/log/$APP_NAME
sudo chown $USER:$USER /var/log/$APP_NAME

log "Starting deployment of $APP_NAME"

# Check if Docker and Docker Compose are installed
if ! command -v docker &> /dev/null; then
    error "Docker is not installed"
fi

if ! command -v docker &> /dev/null || ! docker compose version &> /dev/null; then
    error "Docker Compose is not installed or not working properly"
fi

# Create deployment directory
log "Creating deployment directory"
sudo mkdir -p "$DEPLOY_PATH"
sudo chown $USER:$USER "$DEPLOY_PATH"

# Create backup directory
log "Creating backup directory"
sudo mkdir -p "$BACKUP_PATH"
sudo chown $USER:$USER "$BACKUP_PATH"

# Backup current deployment if it exists
if [ -d "$DEPLOY_PATH/current" ]; then
    log "Creating backup of current deployment"
    BACKUP_NAME="backup-$(date +%Y%m%d-%H%M%S)"
    cp -r "$DEPLOY_PATH/current" "$BACKUP_PATH/$BACKUP_NAME"
    
    # Keep only last 5 backups
    cd "$BACKUP_PATH"
    ls -t | tail -n +6 | xargs -r rm -rf
fi

# Create new deployment directory
log "Creating new deployment directory"
DEPLOY_TIMESTAMP=$(date +%Y%m%d-%H%M%S)
NEW_DEPLOY_PATH="$DEPLOY_PATH/releases/$DEPLOY_TIMESTAMP"
mkdir -p "$NEW_DEPLOY_PATH"

# Copy application files
log "Copying application files"
cp -r . "$NEW_DEPLOY_PATH/"

# Set proper permissions
log "Setting file permissions"
sudo chown -R www-data:www-data "$NEW_DEPLOY_PATH/storage"
sudo chown -R www-data:www-data "$NEW_DEPLOY_PATH/bootstrap/cache"
sudo chmod -R 755 "$NEW_DEPLOY_PATH/storage"
sudo chmod -R 755 "$NEW_DEPLOY_PATH/bootstrap/cache"

# Create .env file if it doesn't exist
if [ ! -f "$NEW_DEPLOY_PATH/.env" ]; then
    log "Creating .env file"
    cp "$NEW_DEPLOY_PATH/.env.example" "$NEW_DEPLOY_PATH/.env" 2>/dev/null || {
        cat > "$NEW_DEPLOY_PATH/.env" << EOF
APP_NAME="Sachs Admin"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sachs_admin
DB_USERNAME=sachs_admin
DB_PASSWORD=sachs_admin_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF
    }
fi

# Generate application key
log "Generating application key"
cd "$NEW_DEPLOY_PATH"
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --no-interaction

# Run database migrations
log "Running database migrations"
docker compose -f docker-compose.prod.yml run --rm app php artisan migrate --force

# Clear and cache configuration
log "Optimizing application"
docker compose -f docker-compose.prod.yml run --rm app php artisan config:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan route:cache
docker compose -f docker-compose.prod.yml run --rm app php artisan view:cache

# Build and start containers
log "Building and starting containers"
cd "$NEW_DEPLOY_PATH"
docker compose -f docker-compose.prod.yml down --remove-orphans
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
log "Waiting for services to be ready"
sleep 30

# Health check
log "Performing health check"
if curl -f http://localhost/health > /dev/null 2>&1; then
    log "Health check passed"
else
    error "Health check failed"
fi

# Update current symlink
log "Updating current symlink"
rm -f "$DEPLOY_PATH/current"
ln -s "$NEW_DEPLOY_PATH" "$DEPLOY_PATH/current"

# Clean up old releases (keep last 5)
log "Cleaning up old releases"
cd "$DEPLOY_PATH/releases"
ls -t | tail -n +6 | xargs -r rm -rf

# Restart nginx if needed
log "Restarting nginx"
sudo systemctl restart nginx

log "Deployment completed successfully!"
log "Application is available at: http://$(hostname -I | awk '{print $1}')"

# Show container status
log "Container status:"
docker compose -f "$DEPLOY_PATH/current/docker-compose.prod.yml" ps 