# Redis Setup Guide for SubdiPass

## Current Status
Redis configuration is temporarily disabled in favor of file-based drivers due to missing PHP Redis extension.

## Why Redis?
Redis will be used for:
- **Cache** - Fast data caching for improved performance
- **Sessions** - Secure session storage with auto-expiration
- **Queues** - Background job processing (QR generation, notifications)

## Installation Steps

### 1. Enable Redis Extension in PHP

#### For Laragon (Windows):
1. **Download PHP Redis Extension**
   - Go to: https://pecl.php.net/package/redis
   - Or: https://windows.php.net/downloads/pecl/releases/redis/
   - Download the appropriate DLL for your PHP version (8.1 NTS x64)

2. **Install Extension**
   ```
   - Extract php_redis.dll
   - Copy to: C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\ext\
   ```

3. **Enable in php.ini**
   ```ini
   # Add this line to php.ini:
   extension=redis
   ```

4. **Restart Apache**
   - Right-click Laragon tray icon
   - Click "Reload Apache"

5. **Verify Installation**
   ```bash
   php -m | grep redis
   # Should output: redis
   ```

### 2. Start Redis Server

#### Using Laragon:
1. Right-click Laragon tray icon
2. Redis → Start Redis

Or manually:
```bash
C:\laragon\bin\redis\redis-x64-5.0.14.1\redis-server.exe
```

### 3. Test Connection
```bash
cd C:\laragon\www\subdivipass
php artisan tinker

# In tinker:
Redis::set('test', 'Hello Redis!');
Redis::get('test');
# Should return: "Hello Redis!"
```

### 4. Re-enable Redis Configuration

Once Redis is working, update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

Then clear configuration:
```bash
php artisan config:clear
php artisan cache:clear
```

## Verify Redis is Working

### Check Redis Connection
```bash
php artisan redis:ping
# Should return: PONG
```

### Monitor Redis
```bash
# Connect to Redis CLI
redis-cli

# Monitor commands
MONITOR

# Check keys
KEYS *

# Get server info
INFO
```

## Troubleshooting

### Redis Connection Failed
- Ensure Redis server is running
- Check REDIS_HOST and REDIS_PORT in .env
- Verify firewall isn't blocking port 6379

### Extension Not Loading
- Check PHP version matches DLL version
- Verify extension=redis is in correct php.ini
- Check PHP error log: C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\error.log

### Performance Issues
- Increase Redis maxmemory in redis.conf
- Enable persistence if needed
- Monitor with: redis-cli --stat

## Alternative: Docker Redis (Optional)

If native installation is difficult:
```bash
docker run -d -p 6379:6379 --name redis redis:alpine
```

## Production Considerations

When deploying to production:
1. Enable Redis persistence (RDB or AOF)
2. Set strong Redis password
3. Configure maxmemory-policy
4. Enable SSL/TLS for remote connections
5. Set up Redis monitoring

---

**Note:** This is a development setup. Production Redis requires additional security and configuration.
