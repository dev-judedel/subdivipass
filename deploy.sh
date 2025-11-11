#!/bin/bash

###############################################################################
# SubdiPass - Production Deployment Script
# For Hostinger VPS Deployment
###############################################################################

set -e  # Exit on error

echo "🚀 Starting SubdiPass Deployment..."
echo "=================================="

# Configuration
APP_DIR="/home/username/subdivipass"
BACKUP_DIR="/home/username/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Backup current state
echo -e "${YELLOW}📦 Creating backup...${NC}"
mkdir -p $BACKUP_DIR
mysqldump -u root -p subdivipass_production > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"
tar -czf "$BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz" storage public/storage
echo -e "${GREEN}✓ Backup created${NC}"

# Step 2: Enable maintenance mode
echo -e "${YELLOW}🔧 Enabling maintenance mode...${NC}"
cd $APP_DIR
php artisan down --render="503" --secret="deployment-token-$(openssl rand -hex 8)"
echo -e "${GREEN}✓ Maintenance mode enabled${NC}"

# Step 3: Pull latest code
echo -e "${YELLOW}📥 Pulling latest code from Git...${NC}"
git pull origin main
echo -e "${GREEN}✓ Code updated${NC}"

# Step 4: Install/Update dependencies
echo -e "${YELLOW}📚 Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Composer dependencies installed${NC}"

echo -e "${YELLOW}📦 Installing NPM dependencies and building assets...${NC}"
npm ci
npm run build
echo -e "${GREEN}✓ Frontend assets built${NC}"

# Step 5: Run database migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 6: Clear and optimize caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

echo -e "${YELLOW}⚡ Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✓ Application optimized${NC}"

# Step 7: Storage permissions
echo -e "${YELLOW}🔐 Setting file permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 8: Restart queue workers
echo -e "${YELLOW}🔄 Restarting queue workers...${NC}"
php artisan queue:restart
echo -e "${GREEN}✓ Queue workers restarted${NC}"

# Step 9: Disable maintenance mode
echo -e "${YELLOW}✅ Disabling maintenance mode...${NC}"
php artisan up
echo -e "${GREEN}✓ Application is now live!${NC}"

# Step 10: Health check
echo -e "${YELLOW}🏥 Running health check...${NC}"
HTTP_STATUS=$(curl -o /dev/null -s -w "%{http_code}" https://subdivipass.com)
if [ $HTTP_STATUS -eq 200 ]; then
    echo -e "${GREEN}✓ Health check passed (HTTP $HTTP_STATUS)${NC}"
else
    echo -e "${RED}⚠ Health check failed (HTTP $HTTP_STATUS)${NC}"
    echo "Please investigate immediately!"
fi

echo ""
echo -e "${GREEN}=================================="
echo "🎉 Deployment completed successfully!"
echo "==================================${NC}"
echo ""
echo "Backup Location: $BACKUP_DIR/db_backup_$TIMESTAMP.sql"
echo "Deployment Time: $(date)"
echo ""
