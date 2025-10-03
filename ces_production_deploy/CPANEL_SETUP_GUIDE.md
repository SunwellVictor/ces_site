# Clark English Learning - cPanel Setup Guide

This guide walks you through setting up your Clark English Learning site on cPanel hosting (GreenGeeks or similar).

## Table of Contents

1. [Pre-Setup Checklist](#pre-setup-checklist)
2. [Domain Configuration](#domain-configuration)
3. [File Upload](#file-upload)
4. [Database Setup](#database-setup)
5. [Email Configuration](#email-configuration)
6. [SSL Certificate](#ssl-certificate)
7. [PHP Configuration](#php-configuration)
8. [Cron Jobs](#cron-jobs)
9. [Final Testing](#final-testing)

## Pre-Setup Checklist

Before starting, ensure you have:
- [ ] cPanel login credentials
- [ ] Domain name pointed to your hosting account
- [ ] The `ces_production_deploy` folder ready for upload
- [ ] Stripe account with production keys (if using payments)

## 1. Domain Configuration

### Option A: Point Document Root to /public (Recommended)

1. **Log into cPanel**
2. **Navigate to Domains section**
   - Look for "Subdomains" or "Addon Domains" or "Domains"
3. **Set Document Root**
   - Find your domain in the list
   - Click "Manage" or edit icon
   - Change "Document Root" to: `/public_html/public`
   - Save changes

### Option B: Move Public Files (Alternative)

If you can't change document root:
1. Upload all files to `/public_html/`
2. Move contents of `public/` folder to `/public_html/`
3. Delete the empty `public/` folder
4. Edit `index.php` in root:
   ```php
   // Change these lines:
   require_once __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   
   // To:
   require_once __DIR__.'/vendor/autoload.php';
   $app = require_once __DIR__.'/bootstrap/app.php';
   ```

## 2. File Upload

### Using cPanel File Manager

1. **Open File Manager**
   - In cPanel, click "File Manager"
   - Navigate to your domain's directory (usually `public_html`)

2. **Upload Files**
   - Compress your `ces_production_deploy` folder into a ZIP file
   - Click "Upload" in File Manager
   - Select your ZIP file and upload
   - Extract the ZIP file
   - Move all contents from the extracted folder to your domain's root

3. **Set File Permissions**
   - Select the `storage` folder
   - Right-click → "Change Permissions"
   - Set to 755 (or 775 if 755 doesn't work)
   - Check "Recurse into subdirectories"
   - Apply changes
   - Repeat for `bootstrap/cache` folder

### Using FTP/SFTP

If you prefer FTP:
1. Use an FTP client (FileZilla, etc.)
2. Connect to your hosting account
3. Upload all files to your domain's directory
4. Set permissions as described above

## 3. Database Setup

### Create Database

1. **In cPanel, go to "MySQL Databases"**
2. **Create a new database:**
   - Database name: `clarkenglish_main` (or your preferred name)
   - Click "Create Database"

3. **Create database user:**
   - Username: `clarkenglish_user` (or your preferred name)
   - Password: Generate a strong password
   - Click "Create User"

4. **Add user to database:**
   - Select your database and user
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"

5. **Note down these details:**
   - Database name: `your_cpanel_username_clarkenglish_main`
   - Username: `your_cpanel_username_clarkenglish_user`
   - Password: (the password you created)
   - Host: `localhost`

### Configure Environment

1. **Rename `.env.example` to `.env`**
2. **Update database settings:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_cpanel_username_clarkenglish_main
   DB_USERNAME=your_cpanel_username_clarkenglish_user
   DB_PASSWORD=your_database_password
   ```

### Run Database Setup

**Option A: Using SSH (if available)**
```bash
cd /path/to/your/domain
php setup_database.php
```

**Option B: Using cPanel Terminal (if available)**
1. Open "Terminal" in cPanel
2. Navigate to your domain directory
3. Run: `php setup_database.php`

**Option C: Manual Setup**
1. Go to cPanel → phpMyAdmin
2. Select your database
3. Import the `manual_database_setup.sql` file
4. Run these commands via Terminal or create a temporary PHP file:
   ```php
   <?php
   // Create a file called run_setup.php in your domain root
   require_once 'vendor/autoload.php';
   $app = require_once 'bootstrap/app.php';
   
   // Run artisan commands
   Artisan::call('migrate', ['--force' => true]);
   Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
   Artisan::call('storage:link');
   Artisan::call('config:cache');
   Artisan::call('route:cache');
   Artisan::call('view:cache');
   
   echo "Setup complete!";
   ?>
   ```
   Visit `https://yourdomain.com/run_setup.php` then delete the file.

## 4. Email Configuration

### Set up Email Account

1. **In cPanel, go to "Email Accounts"**
2. **Create email account:**
   - Email: `noreply@yourdomain.com`
   - Password: Generate strong password
   - Mailbox quota: 250 MB (sufficient for notifications)

3. **Get SMTP settings:**
   - Usually found in cPanel → "Email Accounts" → "Connect Devices"
   - Incoming/Outgoing server: `mail.yourdomain.com`
   - Port: 587 (TLS) or 465 (SSL)

4. **Update .env file:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mail.yourdomain.com
   MAIL_PORT=587
   MAIL_USERNAME=noreply@yourdomain.com
   MAIL_PASSWORD=your_email_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@yourdomain.com"
   MAIL_FROM_NAME="Clark English Learning"
   ```

## 5. SSL Certificate

### Enable SSL

1. **In cPanel, find "SSL/TLS"**
2. **Go to "Let's Encrypt" or "SSL Certificates"**
3. **Enable SSL for your domain**
   - Most hosts provide free Let's Encrypt certificates
   - Follow the wizard to install

4. **Force HTTPS (if not automatic):**
   - In cPanel → "Redirects"
   - Create redirect from HTTP to HTTPS
   - Or ensure your `.htaccess` includes HTTPS redirect

5. **Update .env:**
   ```env
   APP_URL=https://yourdomain.com
   ```

## 6. PHP Configuration

### Check PHP Version

1. **In cPanel, go to "PHP Selector" or "MultiPHP Manager"**
2. **Ensure PHP 8.1 or 8.2 is selected**
3. **Enable required extensions:**
   - BCMath
   - Ctype
   - Fileinfo
   - JSON
   - Mbstring
   - OpenSSL
   - PDO
   - Tokenizer
   - XML
   - cURL
   - GD (for image processing)

### PHP Settings

If available, adjust these settings:
- `memory_limit`: 256M or higher
- `max_execution_time`: 300
- `upload_max_filesize`: 64M
- `post_max_size`: 64M

## 7. Cron Jobs

### Set up Laravel Scheduler

1. **In cPanel, go to "Cron Jobs"**
2. **Add new cron job:**
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   - Command: `/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1`

   (Replace `/home/yourusername/public_html/` with your actual path)

## 8. Final Testing

### Create Admin User

1. **Via SSH/Terminal:**
   ```bash
   php artisan user:promote your-email@domain.com admin
   ```

2. **Or create a temporary script:**
   ```php
   <?php
   // create_admin.php - Delete after use!
   require_once 'vendor/autoload.php';
   $app = require_once 'bootstrap/app.php';
   
   Artisan::call('user:promote', [
       'email' => 'your-email@domain.com',
       'role' => 'admin'
   ]);
   echo "Admin user created!";
   ?>
   ```

### Test Your Site

Visit these URLs to verify everything works:

- [ ] **Main site:** `https://yourdomain.com`
- [ ] **User registration:** `https://yourdomain.com/register`
- [ ] **User login:** `https://yourdomain.com/login`
- [ ] **Blog:** `https://yourdomain.com/blog`
- [ ] **Products:** `https://yourdomain.com/products`
- [ ] **Admin panel:** `https://yourdomain.com/admin`

### Configure Stripe (if using payments)

1. **Log into Stripe Dashboard**
2. **Go to Developers → Webhooks**
3. **Add endpoint:** `https://yourdomain.com/webhooks/stripe`
4. **Select events:**
   - `checkout.session.completed`
   - `payment_intent.succeeded`
5. **Copy webhook secret to .env:**
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
   ```

## Troubleshooting

### Common Issues

**500 Internal Server Error:**
- Check file permissions (storage and bootstrap/cache should be 755/775)
- Verify .env configuration
- Check error logs in cPanel

**Database Connection Error:**
- Verify database credentials in .env
- Ensure database user has proper permissions
- Check if database name includes cPanel username prefix

**Email Not Sending:**
- Verify SMTP credentials
- Check if hosting provider requires authentication
- Test with a simple mail script

**Assets Not Loading:**
- Ensure public/build directory was uploaded
- Check if document root is set correctly
- Verify .htaccess rules are working

### Getting Help

1. **Check Laravel logs:** `storage/logs/laravel.log`
2. **Check cPanel error logs:** Usually in cPanel → "Error Logs"
3. **Contact hosting support** for server-specific issues
4. **Test in staging** before making changes to production

## Security Checklist

After deployment:

- [ ] Remove any temporary setup files
- [ ] Verify .env file is not publicly accessible
- [ ] Test that /admin requires authentication
- [ ] Confirm storage directory is protected
- [ ] Enable automatic backups in cPanel
- [ ] Set up monitoring/uptime checks

## Performance Optimization

For better performance:

- [ ] Enable cPanel caching if available
- [ ] Use Cloudflare or similar CDN
- [ ] Optimize images before uploading
- [ ] Monitor database performance
- [ ] Set up log rotation

---

🎉 **Congratulations!** Your Clark English Learning site should now be live and fully functional!