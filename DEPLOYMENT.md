# SubdiPass - Hostinger Deployment Guide

## 📋 Pre-Deployment Checklist

### ✅ Development Environment
- [ ] All tests passing
- [ ] No critical bugs
- [ ] Database migrations tested
- [ ] QR scanner tested on mobile devices
- [ ] PWA installation tested
- [ ] Offline mode verified
- [ ] All features working as expected

### ✅ Code Quality
- [ ] Code reviewed
- [ ] Security vulnerabilities checked
- [ ] Performance optimized
- [ ] Assets minified
- [ ] Dependencies updated
- [ ] No console errors
- [ ] No debug statements

### ✅ Documentation
- [ ] README.md updated
- [ ] CHANGELOG.md updated
- [ ] API documentation complete
- [ ] Environment variables documented
- [ ] Backup procedures documented
- [ ] Rollback plan ready

---

## 🖥️ Hostinger VPS Setup

### 1. Server Requirements
```
- OS: Ubuntu 20.04 LTS or later
- PHP: 8.1 or higher
- MySQL: 8.0 or higher
- Redis: 5.0 or higher
- Nginx or Apache
- Composer 2.x
- Node.js 18.x or higher
- NPM 9.x or higher
```

### 2. Install Required Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis \
    php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-curl \
    php8.1-gd php8.1-zip php8.1-intl

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx
sudo systemctl enable nginx

# Install Supervisor (for queue workers)
sudo apt install -y supervisor
```

### 3. Create Database

```bash
sudo mysql -u root -p

CREATE DATABASE subdivipass_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'subdivipass'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON subdivipass_production.* TO 'subdivipass'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📁 Project Setup

### 1. Clone Repository

```bash
cd /home/username
git clone https://github.com/yourusername/subdivipass.git
cd subdivipass
```

### 2. Configure Environment

```bash
# Copy production environment template
cp .env.production.example .env

# Edit environment variables
nano .env

# Generate application key
php artisan key:generate

# Generate QR signature key
php artisan key:generate --show  # Copy this to QR_SIGNATURE_KEY
```

### 3. Install Dependencies

```bash
# Install Composer packages (production)
composer install --no-dev --optimize-autoloader

# Install NPM packages
npm ci

# Build production assets
npm run build
```

### 4. Set Up Storage

```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public
```

### 5. Run Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed essential data (roles, permissions)
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=PassTypeSeeder
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name subdivipass.com www.subdivipass.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name subdivipass.com www.subdivipass.com;
    root /home/username/subdivipass/public;

    index index.php index.html index.htm;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/subdivipass.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/subdivipass.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(self), microphone=(), geolocation=()" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    # Client body size (for file uploads)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|woff|woff2|ttf|svg|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Save to: `/etc/nginx/sites-available/subdivipass`

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/subdivipass /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d subdivipass.com -d www.subdivipass.com

# Auto-renewal
sudo systemctl enable certbot.timer
```

---

## 🔄 Queue Workers Setup

### Supervisor Configuration

```bash
sudo nano /etc/supervisor/conf.d/subdivipass-worker.conf
```

```ini
[program:subdivipass-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/subdivipass/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/username/subdivipass/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start subdivipass-worker:*
```

---

## ⏰ Cron Jobs (Laravel Scheduler)

```bash
sudo crontab -e -u www-data
```

Add:
```
* * * * * cd /home/username/subdivipass && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Optimization

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize

# Clear application cache
php artisan cache:clear
```

---

## 📊 Monitoring Setup

### Application Monitoring

```bash
# Install Laravel Telescope (optional - development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Server Monitoring

1. **Uptime Monitoring**: Use UptimeRobot or Pingdom
2. **Error Tracking**: Install Sentry
3. **Performance**: Install New Relic or Laravel Debugbar (dev)
4. **Logs**: Centralize with Papertrail or Loggly

---

## 💾 Backup Strategy

### Automated Database Backups

```bash
# Create backup script
sudo nano /home/username/backup.sh
```

```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/home/username/backups"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u subdivipass -p subdivipass_production | gzip > "$BACKUP_DIR/db_$TIMESTAMP.sql.gz"

# File backup
tar -czf "$BACKUP_DIR/files_$TIMESTAMP.tar.gz" /home/username/subdivipass/storage

# Delete backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $TIMESTAMP"
```

```bash
chmod +x /home/username/backup.sh

# Schedule daily backups
crontab -e
0 2 * * * /home/username/backup.sh
```

---

## 🚀 Deployment Process

### First Deployment

```bash
cd /home/username/subdivipass

# Make deployment script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### Subsequent Deployments

```bash
# Pull latest code
git pull origin main

# Run deployment script
./deploy.sh
```

---

## 🔙 Rollback Plan

### Quick Rollback

```bash
# Enable maintenance mode
php artisan down

# Restore database
mysql -u subdivipass -p subdivipass_production < /home/username/backups/db_TIMESTAMP.sql

# Restore files if needed
tar -xzf /home/username/backups/files_TIMESTAMP.tar.gz -C /home/username/subdivipass/

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart queue workers
php artisan queue:restart

# Disable maintenance mode
php artisan up
```

---

## 🧪 Post-Deployment Testing

### Manual Tests
- [ ] Login as each role (super-admin, admin, employee, guard)
- [ ] Create a new pass
- [ ] Generate QR code
- [ ] Scan QR code with guard scanner
- [ ] Test manual PIN validation
- [ ] Test offline mode
- [ ] Verify notifications
- [ ] Check reports generation
- [ ] Test user management
- [ ] Verify subdivision/gate management

### Automated Health Checks
```bash
# API health check
curl https://subdivipass.com/api/health

# Database connection check
php artisan tinker
>>> DB::connection()->getPdo();

# Redis connection check
php artisan tinker
>>> Redis::ping();

# Queue status
php artisan queue:monitor

# Check logs
tail -f storage/logs/laravel.log
```

---

## 🐛 Troubleshooting

### Common Issues

**500 Internal Server Error**
```bash
# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Check permissions
ls -la storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
```

**Queue not processing**
```bash
# Check supervisor
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart subdivipass-worker:*

# Check queue connection
php artisan queue:failed
```

**Database connection error**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check credentials in .env
cat .env | grep DB_
```

---

## 📞 Support Contacts

- **Developer**: [Your Email]
- **Server Admin**: [Admin Email]
- **Hostinger Support**: support@hostinger.com

---

## 📚 Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx Configuration Guide](https://nginx.org/en/docs/)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Supervisor Documentation](http://supervisord.org/)

---

**Last Updated**: November 2024
**Version**: 1.0.0
