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
