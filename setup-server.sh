#!/bin/bash

# Hetzner Server Setup Script for Sachs Admin
# This script sets up a fresh Hetzner server for deployment

set -e

# Configuration
APP_NAME="sachs-admin"
DEPLOY_USER="deploy"
DEPLOY_PATH="/var/www/$APP_NAME"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root"
fi

log "Starting Hetzner server setup for $APP_NAME"

# Update system
log "Updating system packages"
apt update && apt upgrade -y

# Install essential packages
log "Installing essential packages"
apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    htop \
    vim \
    ufw \
    fail2ban \
    nginx \
    certbot \
    python3-certbot-nginx

# Install Docker
log "Installing Docker"
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Create docker-compose symlink for compatibility
log "Setting up Docker Compose compatibility"
ln -sf /usr/libexec/docker/cli-plugins/docker-compose /usr/local/bin/docker-compose

# Create deploy user
log "Creating deploy user"
if ! id "$DEPLOY_USER" &>/dev/null; then
    useradd -m -s /bin/bash "$DEPLOY_USER"
    usermod -aG docker "$DEPLOY_USER"
    usermod -aG sudo "$DEPLOY_USER"
    echo "$DEPLOY_USER ALL=(ALL) NOPASSWD:ALL" | tee /etc/sudoers.d/$DEPLOY_USER
else
    log "Deploy user already exists"
fi

# Create application directories
log "Creating application directories"
mkdir -p "$DEPLOY_PATH"
mkdir -p "/var/backups/$APP_NAME"
mkdir -p "/var/log/$APP_NAME"
chown -R "$DEPLOY_USER:$DEPLOY_USER" "$DEPLOY_PATH"
chown -R "$DEPLOY_USER:$DEPLOY_USER" "/var/backups/$APP_NAME"
chown -R "$DEPLOY_USER:$DEPLOY_USER" "/var/log/$APP_NAME"

# Configure firewall
log "Configuring firewall"
ufw --force enable
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp

# Configure fail2ban
log "Configuring fail2ban"
systemctl enable fail2ban
systemctl start fail2ban

# Configure nginx
log "Configuring nginx"
cat > /etc/nginx/sites-available/$APP_NAME << EOF
server {
    listen 80;
    server_name _;
    
    # Redirect all traffic to Docker containers
    location / {
        proxy_pass http://127.0.0.1:80;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

# Enable nginx site
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test nginx configuration
nginx -t

# Start and enable services
log "Starting and enabling services"
systemctl enable docker
systemctl start docker
systemctl enable nginx
systemctl start nginx

# Configure system limits
log "Configuring system limits"
cat >> /etc/security/limits.conf << EOF
* soft nofile 65536
* hard nofile 65536
* soft nproc 32768
* hard nproc 32768
EOF

# Configure sysctl for better performance
log "Configuring sysctl for better performance"
cat >> /etc/sysctl.conf << EOF
# Increase file descriptor limits
fs.file-max = 65536

# Increase network buffer sizes
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 65536 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216

# Enable TCP fast open
net.ipv4.tcp_fastopen = 3

# Increase connection backlog
net.core.somaxconn = 65535
net.ipv4.tcp_max_syn_backlog = 65535

# Enable TCP window scaling
net.ipv4.tcp_window_scaling = 1

# Enable TCP timestamps
net.ipv4.tcp_timestamps = 1

# Enable TCP selective acknowledgments
net.ipv4.tcp_sack = 1

# Enable TCP FACK
net.ipv4.tcp_fack = 1

# Enable TCP congestion control
net.ipv4.tcp_congestion_control = bbr
EOF

# Apply sysctl changes
sysctl -p

# Create swap file if not exists
if [ ! -f /swapfile ]; then
    log "Creating swap file"
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

# Configure logrotate
log "Configuring logrotate"
cat > /etc/logrotate.d/$APP_NAME << EOF
/var/log/$APP_NAME/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 $DEPLOY_USER $DEPLOY_USER
    postrotate
        systemctl reload nginx
    endscript
}
EOF

# Set up automatic security updates
log "Setting up automatic security updates"
apt install -y unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades

# Create monitoring script
log "Creating monitoring script"
cat > /usr/local/bin/monitor-$APP_NAME << 'EOF'
#!/bin/bash
APP_NAME="sachs-admin"
DEPLOY_PATH="/var/www/$APP_NAME"

# Check if containers are running
if [ -d "$DEPLOY_PATH/current" ]; then
    cd "$DEPLOY_PATH/current"
    if ! docker compose -f docker-compose.prod.yml ps | grep -q "Up"; then
        echo "$(date): Containers are down, restarting..." >> /var/log/$APP_NAME/monitor.log
        docker compose -f docker-compose.prod.yml up -d
    fi
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 80 ]; then
    echo "$(date): Disk usage is high: ${DISK_USAGE}%" >> /var/log/$APP_NAME/monitor.log
fi

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ "$MEM_USAGE" -gt 80 ]; then
    echo "$(date): Memory usage is high: ${MEM_USAGE}%" >> /var/log/$APP_NAME/monitor.log
fi
EOF

chmod +x /usr/local/bin/monitor-$APP_NAME

# Add monitoring to crontab
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/monitor-$APP_NAME") | crontab -

# Create backup script
log "Creating backup script"
cat > /usr/local/bin/backup-$APP_NAME << 'EOF'
#!/bin/bash
APP_NAME="sachs-admin"
DEPLOY_PATH="/var/www/$APP_NAME"
BACKUP_PATH="/var/backups/$APP_NAME"
DATE=$(date +%Y%m%d-%H%M%S)

if [ -d "$DEPLOY_PATH/current" ]; then
    # Backup application files
    tar -czf "$BACKUP_PATH/app-$DATE.tar.gz" -C "$DEPLOY_PATH" current/
    
    # Backup database
    cd "$DEPLOY_PATH/current"
    docker compose -f docker-compose.prod.yml exec -T db mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" sachs_admin > "$BACKUP_PATH/db-$DATE.sql"
    
    # Keep only last 7 backups
    cd "$BACKUP_PATH"
    ls -t app-*.tar.gz | tail -n +8 | xargs -r rm -f
    ls -t db-*.sql | tail -n +8 | xargs -r rm -f
    
    echo "$(date): Backup completed" >> /var/log/$APP_NAME/backup.log
fi
EOF

chmod +x /usr/local/bin/backup-$APP_NAME

# Add backup to crontab (daily at 2 AM)
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/backup-$APP_NAME") | crontab -

log "Server setup completed successfully!"
log "Next steps:"
log "1. Switch to deploy user: su - $DEPLOY_USER"
log "2. Clone your repository to $DEPLOY_PATH"
log "3. Run the deployment script: ./deploy.sh"
log "4. Configure your domain and SSL certificate" 