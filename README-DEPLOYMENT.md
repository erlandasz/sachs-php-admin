# Sachs Admin - Deployment Guide for Hetzner

This guide will help you deploy the Sachs Admin Laravel application to a Hetzner server using Docker containers.

## Prerequisites

- A Hetzner Cloud server (recommended: CX21 or higher)
- Ubuntu 22.04 LTS or later
- Root access to the server
- A domain name pointing to your server (optional but recommended)

## Server Setup

### 1. Initial Server Configuration

Connect to your Hetzner server as root and run the setup script:

```bash
# Copy the setup script to the server
scp setup-server.sh root@YOUR_SERVER_IP:/tmp/
ssh root@YOUR_SERVER_IP "bash /tmp/setup-server.sh"
```

This script will:
- Update the system
- Install Docker and Docker Compose
- Configure firewall and security
- Set up nginx as a reverse proxy
- Create a deploy user
- Configure system optimizations
- Set up monitoring and backup scripts

### 2. Manual Setup (Alternative)

If you prefer to set up manually:

```bash
# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Create deploy user
useradd -m -s /bin/bash deploy
usermod -aG docker deploy
usermod -aG sudo deploy
echo "deploy ALL=(ALL) NOPASSWD:ALL" | tee /etc/sudoers.d/deploy
```

## Application Deployment

### 1. Clone the Repository

Switch to the deploy user and clone your repository:

```bash
su - deploy
cd /var/www
git clone https://github.com/erlandasz/sachs-admin.git
cd sachs-admin
```

### 2. Configure Environment Variables

Create a `.env` file with your production settings:

```bash
cp .env.example .env
nano .env
```

Update the following variables:

```env
APP_NAME="Sachs Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sachs_admin
DB_USERNAME=sachs_admin
DB_PASSWORD=your_secure_password

REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

CLOUDINARY_URL=your-cloudinary-url
SENTRY_LARAVEL_DSN=your-sentry-dsn
AIRTABLE_API_KEY=your-airtable-key
AIRTABLE_BASE_ID=your-airtable-base-id
```

### 3. Deploy the Application

Run the deployment script:

```bash
chmod +x deploy.sh
./deploy.sh
```

This script will:
- Build the Docker containers
- Set up the database
- Run migrations
- Optimize the application
- Start all services
- Perform health checks

### 4. Manual Deployment (Alternative)

If you prefer to deploy manually:

```bash
# Build and start containers
docker-compose -f docker-compose.prod.yml down --remove-orphans
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d

# Run Laravel setup commands
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
```

## SSL Certificate Setup

### Using Let's Encrypt

1. Install Certbot:

```bash
sudo apt install certbot python3-certbot-nginx
```

2. Obtain SSL certificate:

```bash
sudo certbot --nginx -d your-domain.com
```

3. Set up auto-renewal:

```bash
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring and Maintenance

### Health Checks

The application includes health check endpoints:

- Application health: `http://your-domain.com/health`
- Nginx status: `http://your-domain.com/nginx_status`

### Logs

View application logs:

```bash
# Application logs
docker-compose -f docker-compose.prod.yml logs app

# Database logs
docker-compose -f docker-compose.prod.yml logs db

# Nginx logs
docker-compose -f docker-compose.prod.yml logs nginx
```

### Backups

Automatic backups are configured to run daily at 2 AM. Manual backups:

```bash
sudo /usr/local/bin/backup-sachs-admin
```

### Monitoring

The server includes automatic monitoring that:
- Checks if containers are running
- Monitors disk and memory usage
- Restarts services if needed

## Troubleshooting

### Common Issues

1. **Port 80 already in use**
   ```bash
   sudo systemctl stop nginx
   docker-compose -f docker-compose.prod.yml up -d
   ```

2. **Permission denied errors**
   ```bash
   sudo chown -R deploy:deploy /var/www/sachs-admin
   sudo chmod -R 755 /var/www/sachs-admin/storage
   ```

3. **Database connection issues**
   ```bash
   docker-compose -f docker-compose.prod.yml logs db
   docker-compose -f docker-compose.prod.yml restart db
   ```

4. **Application not responding**
   ```bash
   docker-compose -f docker-compose.prod.yml ps
   docker-compose -f docker-compose.prod.yml restart app
   ```

### Performance Optimization

1. **Enable OPcache** (already configured in Docker)
2. **Use Redis for caching** (already configured)
3. **Optimize database queries**
4. **Use CDN for static assets**

### Security Considerations

1. **Firewall**: UFW is configured to allow only necessary ports
2. **Fail2ban**: Protects against brute force attacks
3. **Docker security**: Containers run with minimal privileges
4. **SSL/TLS**: Use Let's Encrypt for free SSL certificates
5. **Regular updates**: Automatic security updates are enabled

## Scaling

### Horizontal Scaling

To scale the application across multiple servers:

1. Use a load balancer (Hetzner Load Balancer)
2. Set up shared storage for uploads
3. Use external database (Hetzner Managed Database)
4. Configure Redis clustering

### Vertical Scaling

To scale on a single server:

1. Increase server resources (CPU, RAM)
2. Optimize Docker resource limits
3. Use external database and Redis services

## Support

For issues and questions:

1. Check the logs: `docker-compose -f docker-compose.prod.yml logs`
2. Review the monitoring logs: `/var/log/sachs-admin/monitor.log`
3. Check system resources: `htop`, `df -h`, `free -h`
4. Verify network connectivity: `curl -I http://localhost/health`

## Maintenance Commands

```bash
# Restart all services
docker-compose -f docker-compose.prod.yml restart

# Update application
git pull
./deploy.sh

# View running containers
docker-compose -f docker-compose.prod.yml ps

# Access application container
docker-compose -f docker-compose.prod.yml exec app bash

# Run artisan commands
docker-compose -f docker-compose.prod.yml exec app php artisan list

# Clear caches
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
``` 