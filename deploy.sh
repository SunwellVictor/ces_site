#!/bin/bash

# Clark English Learning - Production Deployment Package Script
# This script prepares the application for production deployment

echo "🚀 Preparing Clark English Learning for production deployment..."

# Create deployment directory
DEPLOY_DIR="ces_production_deploy"
rm -rf $DEPLOY_DIR
mkdir $DEPLOY_DIR

echo "📦 Creating deployment package..."

# Copy essential application files
cp -r app $DEPLOY_DIR/
cp -r bootstrap $DEPLOY_DIR/
cp -r config $DEPLOY_DIR/
cp -r database $DEPLOY_DIR/
cp -r public $DEPLOY_DIR/
cp -r resources $DEPLOY_DIR/
cp -r routes $DEPLOY_DIR/
cp -r storage $DEPLOY_DIR/

# Root files
cp artisan $DEPLOY_DIR/
cp composer.json $DEPLOY_DIR/
cp composer.lock $DEPLOY_DIR/
- cp .env.production $DEPLOY_DIR/.env.example
+ cp .env.example $DEPLOY_DIR/.env.example

# Build frontend assets before packaging (if npm is available)
+ if command -v npm >/dev/null 2>&1 && [ -f package.json ]; then
+   echo "🧱 Building frontend assets..."
+   if [ ! -d node_modules ]; then
+     npm ci --silent || npm install --silent
+   fi
+   npm run build
+ else
+   echo "⚠️ Skipping asset build: npm not found or package.json missing."
+ fi

# Copy vendor directory (production optimized)
cp -r vendor $DEPLOY_DIR/

# Create storage directories and set permissions
mkdir -p $DEPLOY_DIR/storage/app/private
mkdir -p $DEPLOY_DIR/storage/app/public
mkdir -p $DEPLOY_DIR/storage/framework/cache
mkdir -p $DEPLOY_DIR/storage/framework/sessions
mkdir -p $DEPLOY_DIR/storage/framework/testing
mkdir -p $DEPLOY_DIR/storage/framework/views
mkdir -p $DEPLOY_DIR/storage/logs

# Create .htaccess files for security
cat > $DEPLOY_DIR/storage/.htaccess << 'EOF'
Options -Indexes
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
EOF

cat > $DEPLOY_DIR/storage/app/.htaccess << 'EOF'
Options -Indexes
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
EOF

# Create production .htaccess for public directory
cat > $DEPLOY_DIR/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

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

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Disable server signature
ServerSignature Off

# Hide PHP version
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>
EOF

# Create root .htaccess for additional security
cat > $DEPLOY_DIR/.htaccess << 'EOF'
# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

<Files "artisan">
    Order allow,deny
    Deny from all
</Files>

# Deny access to directories
RedirectMatch 403 ^/storage/.*$
RedirectMatch 403 ^/bootstrap/.*$
RedirectMatch 403 ^/config/.*$
RedirectMatch 403 ^/database/.*$
RedirectMatch 403 ^/routes/.*$
RedirectMatch 403 ^/app/.*$
RedirectMatch 403 ^/vendor/.*$
EOF

# Create deployment instructions
cat > $DEPLOY_DIR/DEPLOYMENT_INSTRUCTIONS.md << 'EOF'
# Clark English Learning - Deployment Instructions

## Quick Start

1. **Upload Files**: Upload all files to your hosting account
2. **Set Document Root**: Point your domain to the `public` directory
3. **Configure Environment**: Rename `.env.example` to `.env` and update values
4. **Set Permissions**: Ensure `storage` and `bootstrap/cache` are writable
5. **Run Setup**: Execute the database setup commands

## Detailed Steps

### 1. File Upload
- Upload all files to your hosting account root directory
- If using cPanel File Manager, upload to `public_html` or your domain's directory

### 2. Document Root Configuration
**Option A: Point Document Root to /public (Recommended)**
- In cPanel > Domains, set document root to `/public_html/public`

**Option B: Move public contents to web root**
- Move contents of `public/` to `public_html/`
- Update `index.php` paths accordingly

### 3. Environment Configuration
- Rename `.env.example` to `.env`
- Update database credentials from cPanel
- Update mail settings for your domain
- Add your Stripe production keys

### 4. Database Setup
Run these commands via SSH or create a setup script:

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. File Permissions
Ensure these directories are writable (755 or 775):
- `storage/` and all subdirectories
- `bootstrap/cache/`

### 6. SSL Configuration
- Enable SSL in cPanel
- Update APP_URL in .env to use https://

## Troubleshooting

### Common Issues
- **500 Error**: Check file permissions and .env configuration
- **Database Connection**: Verify database credentials in .env
- **Missing Assets**: Ensure public/build directory was uploaded
- **Email Issues**: Verify SMTP settings with your hosting provider

### Support
- Check Laravel logs in `storage/logs/`
- Enable APP_DEBUG=true temporarily for detailed errors
- Contact hosting support for server-specific issues
EOF

echo "✅ Deployment package created in '$DEPLOY_DIR' directory"
echo ""
echo "📋 Next Steps:"
echo "1. Compress the '$DEPLOY_DIR' directory"
echo "2. Upload to your hosting account"
echo "3. Follow the DEPLOYMENT_INSTRUCTIONS.md file"
echo "4. Configure your domain's document root"
echo "5. Set up the database and run migrations"
echo ""
echo "🎉 Your Clark English Learning site is ready for deployment!"