# CI/CD Setup Guide for Hetzner Deployment

This guide will help you set up automated CI/CD deployment for your Sachs Admin Laravel application to Hetzner using GitHub Actions.

## Overview

The CI/CD pipeline includes:
- **Testing**: PHP linting, code formatting, and unit tests
- **Building**: Docker image creation and push to GitHub Container Registry
- **Deployment**: Automated deployment to Hetzner server
- **Monitoring**: Health checks and rollback capabilities

## Prerequisites

1. **GitHub Repository**: Your code must be in a GitHub repository
2. **Hetzner Server**: Set up using the `setup-server.sh` script
3. **GitHub Secrets**: Configure required secrets in your repository
4. **GitHub Container Registry**: Enabled for your repository

## Step 1: Configure GitHub Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions, and add the following secrets:

### Required Secrets

```bash
# Hetzner Server Configuration
HETZNER_HOST=your-server-ip
HETZNER_USER=deploy
HETZNER_PORT=22
HETZNER_SSH_KEY=your-private-ssh-key

# Database Configuration
DB_PASSWORD=your-secure-database-password

# Email Configuration
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@your-domain.com

# External Services
CLOUDINARY_URL=your-cloudinary-url
SENTRY_LARAVEL_DSN=your-sentry-dsn
AIRTABLE_API_KEY=your-airtable-key
AIRTABLE_BASE_ID=your-airtable-base-id
```



## Step 2: Generate SSH Key for Deployment

1. **Generate SSH key pair**:
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy
```

2. **Add public key to Hetzner server**:
```bash
# Copy the public key to your Hetzner server
cat ~/.ssh/github_actions_deploy.pub | ssh root@your-server-ip "cat >> ~/.ssh/authorized_keys"
```

3. **Add private key to GitHub Secrets**:
   - Copy the content of `~/.ssh/github_actions_deploy`
   - Add it as `HETZNER_SSH_KEY` secret in GitHub

## Step 3: Enable GitHub Container Registry

1. Go to your repository settings
2. Navigate to "Packages"
3. Ensure "Inherit access from source repository" is enabled
4. The workflow will automatically use `GITHUB_TOKEN` for authentication

## Step 4: Configure Server for CI/CD

Run the server setup script on your Hetzner server:

```bash
# Connect to your Hetzner server as root
ssh root@your-server-ip

# Copy the setup script to the server
scp setup-server.sh root@YOUR_SERVER_IP:/tmp/
ssh root@YOUR_SERVER_IP "bash /tmp/setup-server.sh"
```

## Step 5: Test the CI/CD Pipeline

### Automatic Deployment

1. **Push to main branch**: Any push to the `main` branch will trigger automatic deployment to production
2. **Monitor the workflow**: Go to Actions tab in your GitHub repository to monitor the deployment

### Manual Deployment

1. Go to Actions tab in your GitHub repository
2. Select "Deploy to Hetzner" workflow
3. Click "Run workflow"
4. Click "Run workflow"

## Step 6: Verify Deployment

### Health Checks

The deployment includes automatic health checks:

```bash
# Check application health
curl -f http://your-server-ip/health

# Check nginx status
curl -f http://your-server-ip/nginx_status
```

### Container Status

```bash
# SSH to your server
ssh deploy@your-server-ip

# Check container status
docker compose -f /var/www/sachs-admin/current/docker-compose.prod.yml ps

# View logs
docker compose -f /var/www/sachs-admin/current/docker-compose.prod.yml logs app
```

## Workflow Details

### Jobs Overview

1. **test**: Runs PHP linting, code formatting, and unit tests
2. **build-and-push**: Builds Docker image and pushes to GitHub Container Registry
3. **deploy**: Deploys to production environment

### Deployment Process

1. **Backup**: Creates backup of current deployment
2. **Build**: Pulls latest Docker image from registry
3. **Deploy**: Updates application files and configuration
4. **Database**: Runs migrations
5. **Optimize**: Caches configuration, routes, and views
6. **Health Check**: Verifies application is running
7. **Cleanup**: Removes old deployments (keeps last 5)

## Environment Variables

The CI/CD pipeline automatically sets up environment variables from GitHub secrets:

```env
APP_NAME="Sachs Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.sachsevent.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sachs_admin
DB_USERNAME=sachs_admin
DB_PASSWORD=${DB_PASSWORD}

REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME="Sachs Admin"

CLOUDINARY_URL=${CLOUDINARY_URL}
SENTRY_LARAVEL_DSN=${SENTRY_LARAVEL_DSN}
AIRTABLE_API_KEY=${AIRTABLE_API_KEY}
AIRTABLE_BASE_ID=${AIRTABLE_BASE_ID}
```

## Monitoring and Troubleshooting

### Logs

```bash
# Deployment logs
sudo tail -f /var/log/sachs-admin/deploy.log

# Application logs
docker compose -f /var/www/sachs-admin/current/docker-compose.prod.yml logs app

# Nginx logs
docker compose -f /var/www/sachs-admin/current/docker-compose.prod.yml logs nginx

# Database logs
docker compose -f /var/www/sachs-admin/current/docker-compose.prod.yml logs db
```

### Common Issues

1. **SSH Connection Failed**
   - Verify SSH key is correctly added to GitHub secrets
   - Check server firewall allows SSH connections
   - Ensure deploy user has proper permissions

2. **Docker Image Pull Failed**
   - Verify GitHub Container Registry access
   - Check `GITHUB_TOKEN` permissions
   - Ensure repository has package access enabled

3. **Health Check Failed**
   - Check application logs for errors
   - Verify database connection
   - Check nginx configuration

4. **Database Migration Failed**
   - Verify database credentials
   - Check database server is running
   - Review migration files for errors

### Rollback

If deployment fails, you can rollback to the previous version:

```bash
# SSH to your server
ssh deploy@your-server-ip

# List available backups
ls -la /var/backups/sachs-admin/

# Restore from backup
sudo cp -r /var/backups/sachs-admin/backup-YYYYMMDD-HHMMSS /var/www/sachs-admin/current
sudo chown -R deploy:deploy /var/www/sachs-admin/current
cd /var/www/sachs-admin/current
docker compose -f docker-compose.prod.yml up -d
```

## Security Considerations

1. **SSH Keys**: Use strong SSH keys and rotate them regularly
2. **Secrets**: Never commit secrets to the repository
3. **Firewall**: Ensure only necessary ports are open
4. **Updates**: Keep the server and Docker images updated
5. **Backups**: Regular backups are automatically created

## Performance Optimization

1. **Docker Caching**: The workflow uses GitHub Actions cache for faster builds
2. **Multi-stage Build**: Dockerfile uses multi-stage build for smaller images
3. **OPcache**: PHP OPcache is enabled for better performance
4. **Redis**: Redis is used for caching and sessions
5. **Nginx**: Optimized nginx configuration with gzip compression

## Scaling

### Horizontal Scaling

To scale across multiple servers:

1. Set up load balancer (Hetzner Load Balancer)
2. Configure multiple deployment targets
3. Use external database and Redis services
4. Update CI/CD workflow for multiple environments

### Vertical Scaling

To scale on a single server:

1. Increase server resources (CPU, RAM)
2. Optimize Docker resource limits
3. Use external database and Redis services
4. Configure nginx worker processes

## Support

For issues with the CI/CD pipeline:

1. Check GitHub Actions logs for detailed error messages
2. Review server logs: `/var/log/sachs-admin/deploy.log`
3. Verify all secrets are correctly configured
4. Test SSH connection manually
5. Check Docker and Docker Compose versions on server 