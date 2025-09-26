# Clark English Learning - Production Deployment Guide

This guide covers deploying the Laravel application to GreenGeeks EcoSite Premium hosting.

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Environment Configuration](#environment-configuration)
3. [File Upload & Document Root](#file-upload--document-root)
4. [Database Setup](#database-setup)
5. [Storage & Permissions](#storage--permissions)
6. [Cron Jobs](#cron-jobs)
7. [SSL & HTTPS Configuration](#ssl--https-configuration)
8. [Post-Deployment Commands](#post-deployment-commands)
9. [Testing & Verification](#testing--verification)
10. [Troubleshooting](#troubleshooting)

## Pre-Deployment Checklist

- [ ] Fresh `APP_KEY` generated
- [ ] `.env` file configured for production
- [ ] Database credentials from cPanel
- [ ] SMTP settings from GreenGeeks
- [ ] Stripe production keys (when ready)
- [ ] Domain SSL certificate enabled

## Environment Configuration

### 1. Generate Application Key

```bash
php artisan key:generate --show
```

Copy the generated key for use in your `.env` file.

### 2. Create Production .env File

Create a `.env` file in your application root with the following configuration:

```env
# Application Settings
APP_NAME="Clark English Learning"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://clarkenglish.com

# Localization
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Database Configuration (Get from cPanel > MySQL Databases)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_database_name
DB_USERNAME=your_cpanel_database_user
DB_PASSWORD=your_cpanel_database_password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail Configuration (GreenGeeks SMTP)
MAIL_MAILER=smtp
MAIL_HOST=mail.clarkenglish.com
MAIL_PORT=587
MAIL_USERNAME=noreply@clarkenglish.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@clarkenglish.com"
MAIL_FROM_NAME="Clark English Learning"

# Stripe Configuration (Production Keys)
STRIPE_PUBLIC=pk_live_your_stripe_public_key
STRIPE_SECRET=sk_live_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

### 3. Environment Variables Setup in cPanel

1. Log into cPanel
2. Navigate to **Software > PHP Selector** (if available)
3. Click **Options** and add environment variables
4. Alternatively, use `.env` file in application root

## File Upload & Document Root

### Option 1: Point Document Root to /public (Recommended)

1. In cPanel, go to **Domains > Subdomains/Addon Domains**
2. Edit your domain settings
3. Set **Document Root** to `/public_html/your_app_folder/public`

### Option 2: Move Public Contents to Root (Last Resort)

If you cannot change the document root:

```bash
# Move all contents from public/ to root
mv public/* ./
mv public/.htaccess ./

# Update index.php paths
# Change these lines in index.php:
require_once __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

# To:
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
```

## Database Setup

### 1. Create Database in cPanel

1. Go to **Databases > MySQL Databases**
2. Create a new database: `clarkenglish_prod`
3. Create a database user with full privileges
4. Note the credentials for your `.env` file

### 2. Run Migrations

```bash
php artisan migrate --force
```

### 3. Seed Initial Data

```bash
php artisan db:seed --force
```

## Storage & Permissions

### 1. Create Storage Symlink

```bash
php artisan storage:link
```

### 2. Set Proper Permissions

```bash
# Make storage and cache directories writable
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# If using shared hosting, you might need 755
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 3. Verify .htaccess Files

Ensure these `.htaccess` files exist:

**storage/.htaccess** (blocks direct access):
```apache
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>

<RequireAll>
    Require all denied
</RequireAll>

Options -Indexes
ServerSignature Off
```

**public/.htaccess** (Laravel routing):
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Force HTTPS
    RewriteCond %{HTTPS} !=on
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Cron Jobs

### 1. Set Up Laravel Scheduler

In cPanel > **Advanced > Cron Jobs**, add:

```bash
# Laravel Task Scheduler (runs every minute)
* * * * * /usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

Replace `/home/username/public_html/` with your actual path.

### 2. Optional: Database Backup Cron

```bash
# Daily database backup at 2 AM
0 2 * * * mysqldump -u username -p'password' database_name > /home/username/backups/db_$(date +\%Y\%m\%d).sql
```

### 3. Verify Queue Table

```bash
php artisan queue:table
php artisan migrate --force
```

## SSL & HTTPS Configuration

### 1. Enable SSL in cPanel

1. Go to **Security > SSL/TLS**
2. Enable **Let's Encrypt SSL** (free)
3. Force HTTPS redirects

### 2. Update Application URL

Update your `.env` file:
```env
APP_URL=https://clarkenglish.com
```

### 3. Verify HTTPS Middleware

The application includes `ForceHttps` middleware that:
- Redirects HTTP to HTTPS in production
- Adds HSTS headers for security
- Only activates in production environment

## Post-Deployment Commands

Run these commands after uploading files:

```bash
# Clear and cache configuration
php artisan config:cache

# Cache routes for better performance
php artisan route:cache

# Cache compiled views
php artisan view:cache

# Create storage symlink
php artisan storage:link

# Run database migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force
```

## Testing & Verification

### Manual Testing Checklist

- [ ] **Homepage loads via HTTPS**: Visit `https://clarkenglish.com`
- [ ] **SSL certificate valid**: Check for green lock icon
- [ ] **HTTP redirects to HTTPS**: Visit `http://clarkenglish.com`
- [ ] **Database connection works**: Try logging in
- [ ] **File uploads work**: Test product/file uploads
- [ ] **Email sending works**: Test contact forms
- [ ] **Cron jobs running**: Check `php artisan schedule:run`
- [ ] **Error pages display**: Test 404 and 500 errors
- [ ] **Storage files accessible**: Test file downloads
- [ ] **Admin area secure**: Verify admin authentication

### Security Verification

```bash
# Test .env file is not accessible
curl https://clarkenglish.com/.env
# Should return 404 or 403

# Test storage directory is not accessible
curl https://clarkenglish.com/storage/app/
# Should return 403 or custom error

# Verify HSTS header
curl -I https://clarkenglish.com
# Should include: Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

## Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

**Check error logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common causes:**
- Incorrect file permissions
- Missing `.env` file
- Database connection issues
- Missing vendor dependencies

**Solutions:**
```bash
# Fix permissions
chmod -R 755 storage/ bootstrap/cache/

# Regenerate autoloader
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### 2. Database Connection Issues

**Check database credentials:**
- Verify database name, username, password in `.env`
- Ensure database user has proper privileges
- Test connection from cPanel phpMyAdmin

#### 3. File Upload Issues

**Check storage permissions:**
```bash
chmod -R 775 storage/
php artisan storage:link
```

**Verify disk configuration in `config/filesystems.php`**

#### 4. Email Not Sending

**Test SMTP settings:**
- Verify SMTP credentials in cPanel
- Check firewall/port restrictions
- Test with a simple mail client

#### 5. Cron Jobs Not Running

**Check cron syntax:**
- Verify PHP path: `which php` or `/usr/local/bin/php`
- Check file permissions on artisan
- Test manually: `php artisan schedule:run`

#### 6. SSL Certificate Issues

**Common solutions:**
- Wait 24-48 hours for DNS propagation
- Clear browser cache
- Check domain DNS settings
- Verify SSL is enabled in cPanel

### Log Locations

- **Laravel Logs**: `storage/logs/laravel.log`
- **Web Server Logs**: cPanel > **Metrics > Error Logs**
- **Cron Logs**: cPanel > **Advanced > Cron Jobs** (view logs)

### Performance Optimization

```bash
# Enable OPcache (if available)
# Add to .htaccess or ask hosting provider

# Optimize Composer autoloader
composer install --no-dev --optimize-autoloader

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enable gzip compression in .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### Support Contacts

- **GreenGeeks Support**: Available 24/7 via live chat or ticket
- **Application Issues**: support@clarkenglish.com
- **Emergency Contact**: [Your emergency contact information]

---

**Last Updated**: {{ date('Y-m-d') }}
**Version**: 1.0
**Environment**: Production (GreenGeeks EcoSite Premium)