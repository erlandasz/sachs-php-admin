#!/bin/bash

# Sachs Admin Deployment Diagnostic Script
# This script helps diagnose and fix common deployment issues

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

log "Starting deployment diagnostics..."

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root"
   exit 1
fi

# 1. Check Docker installation
log "1. Checking Docker installation..."
if command -v docker &> /dev/null; then
    info "Docker is installed: $(docker --version)"
else
    error "Docker is not installed"
    info "Run: sudo bash setup-server.sh"
    exit 1
fi

# 2. Check Docker Compose
log "2. Checking Docker Compose..."
if docker compose version &> /dev/null; then
    info "Docker Compose is working: $(docker compose version)"
else
    error "Docker Compose is not working"
    info "Run: sudo bash setup-server.sh"
    exit 1
fi

# 3. Check if user is in docker group
log "3. Checking Docker group membership..."
if groups $USER | grep -q docker; then
    info "User $USER is in docker group"
else
    warning "User $USER is not in docker group"
    info "Run: sudo usermod -aG docker $USER"
    info "Then log out and log back in, or run: newgrp docker"
fi

# 4. Check Docker daemon
log "4. Checking Docker daemon..."
if docker info &> /dev/null; then
    info "Docker daemon is running"
else
    error "Docker daemon is not running"
    info "Run: sudo systemctl start docker"
    exit 1
fi

# 5. Check deployment directory
log "5. Checking deployment directory..."
DEPLOY_PATH="/var/www/sachs-admin"
if [ -d "$DEPLOY_PATH" ]; then
    info "Deployment directory exists: $DEPLOY_PATH"
    if [ -w "$DEPLOY_PATH" ]; then
        info "Deployment directory is writable"
    else
        warning "Deployment directory is not writable"
        info "Run: sudo chown $USER:$USER $DEPLOY_PATH"
    fi
else
    warning "Deployment directory does not exist: $DEPLOY_PATH"
    info "Run: sudo mkdir -p $DEPLOY_PATH && sudo chown $USER:$USER $DEPLOY_PATH"
fi

# 6. Check current deployment
log "6. Checking current deployment..."
if [ -d "$DEPLOY_PATH/current" ]; then
    info "Current deployment exists: $DEPLOY_PATH/current"
    
    # Check docker-compose.prod.yml
    if [ -f "$DEPLOY_PATH/current/docker-compose.prod.yml" ]; then
        info "docker-compose.prod.yml exists"
    else
        error "docker-compose.prod.yml not found in current deployment"
    fi
    
    # Check if containers are running
    cd "$DEPLOY_PATH/current"
    if docker compose -f docker-compose.prod.yml ps &> /dev/null; then
        info "Docker Compose configuration is valid"
        CONTAINERS=$(docker compose -f docker-compose.prod.yml ps -q)
        if [ -n "$CONTAINERS" ]; then
            info "Containers are running:"
            docker compose -f docker-compose.prod.yml ps
        else
            warning "No containers are running"
        fi
    else
        error "Docker Compose configuration is invalid"
    fi
else
    warning "No current deployment found"
fi

# 7. Check network connectivity
log "7. Checking network connectivity..."
if curl -f http://localhost/up &> /dev/null; then
    info "Application health check passed"
elif curl -f http://localhost/ &> /dev/null; then
    info "Application is accessible (main page)"
else
    warning "Application is not accessible"
fi

# 8. Check system resources
log "8. Checking system resources..."
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')

info "Disk usage: ${DISK_USAGE}%"
info "Memory usage: ${MEM_USAGE}%"

if [ "$DISK_USAGE" -gt 80 ]; then
    warning "Disk usage is high: ${DISK_USAGE}%"
fi

if [ "$MEM_USAGE" -gt 80 ]; then
    warning "Memory usage is high: ${MEM_USAGE}%"
fi

# 9. Check logs
log "9. Checking recent logs..."
LOG_FILE="/var/log/sachs-admin/deploy.log"
if [ -f "$LOG_FILE" ]; then
    info "Recent deployment logs:"
    tail -n 20 "$LOG_FILE" 2>/dev/null || warning "Cannot read log file"
else
    warning "No deployment log file found: $LOG_FILE"
fi

# 10. Provide recommendations
log "10. Recommendations:"
echo ""
if ! groups $USER | grep -q docker; then
    echo "  • Add user to docker group: sudo usermod -aG docker $USER"
    echo "  • Then log out and log back in, or run: newgrp docker"
fi

if [ ! -d "$DEPLOY_PATH" ] || [ ! -w "$DEPLOY_PATH" ]; then
    echo "  • Fix deployment directory permissions: sudo mkdir -p $DEPLOY_PATH && sudo chown $USER:$USER $DEPLOY_PATH"
fi

if [ ! -f "$DEPLOY_PATH/current/docker-compose.prod.yml" ]; then
    echo "  • Run deployment script: ./deploy.sh"
fi

echo "  • Check container logs: docker compose -f $DEPLOY_PATH/current/docker-compose.prod.yml logs"
echo "  • Restart containers: docker compose -f $DEPLOY_PATH/current/docker-compose.prod.yml restart"
echo "  • View container status: docker compose -f $DEPLOY_PATH/current/docker-compose.prod.yml ps"

log "Diagnostics completed!"
