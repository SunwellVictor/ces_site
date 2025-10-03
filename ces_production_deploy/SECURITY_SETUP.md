# Clark English Learning - Security Configuration Guide

This guide covers essential security configurations for your production deployment.

## Table of Contents

1. [File Permissions](#file-permissions)
2. [Environment Security](#environment-security)
3. [Web Server Security](#web-server-security)
4. [Database Security](#database-security)
5. [Application Security](#application-security)
6. [Monitoring & Maintenance](#monitoring--maintenance)

## 1. File Permissions

### Critical File Permissions

Set these permissions immediately after upload:

```bash
# Application directories
chmod 755 app/
chmod 755 bootstrap/
chmod 755 config/
chmod 755 database/
chmod 755 public/
chmod 755 resources/
chmod 755 routes/
chmod 755 vendor/

# Storage directories (must be writable)
chmod 775 storage/
chmod 775 storage/app/
chmod 775 storage/app/private/
chmod 775 storage/app/public/
chmod 775 storage/framework/
chmod 775 storage/framework/cache/
chmod 775 storage/framework/sessions/
chmod 775 storage/framework/views/
chmod 775 storage/logs/

# Bootstrap cache (must be writable)
chmod 775 bootstrap/cache/

# Sensitive files (read-only)
chmod 644 .env
chmod 644 composer.json
chmod 644 composer.lock

# Executable files
chmod 755 artisan
```

### Via cPanel File Manager

1. Select folder/file
2. Right-click → "Change Permissions"
3. Set appropriate permissions
4. For directories, check "Recurse into subdirectories"

## 2. Environment Security

### .env File Protection

Your `.env` file contains sensitive information. Ensure it's protected:

**Check .htaccess includes:**
```apache
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

**Verify .env is not web-accessible:**
- Try visiting `https://yourdomain.com/.env`
- Should return 403 Forbidden or 404 Not Found

### Environment Variables Checklist

- [ ] `APP_DEBUG=false` (never true in production)
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` (generated with `php artisan key:generate`)
- [ ] Secure database credentials
- [ ] Production Stripe keys (not test keys)
- [ ] Valid SMTP credentials
- [ ] HTTPS URL in `APP_URL`

## 3. Web Server Security

### .htaccess Security Rules

The deployment includes security-hardened `.htaccess` files:

**Root .htaccess** (protects sensitive files):
```apache
# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

# Block access to application directories
RedirectMatch 403 ^/storage/.*$
RedirectMatch 403 ^/bootstrap/.*$
RedirectMatch 403 ^/config/.*$
```

**Public .htaccess** (includes security headers):
```apache
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

### Additional Security Headers

For enhanced security, add to your public `.htaccess`:

```apache
# Content Security Policy (adjust as needed)
<IfModule mod_headers.c>
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' js.stripe.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self' api.stripe.com; frame-src js.stripe.com;"
</IfModule>

# HSTS (HTTP Strict Transport Security)
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</IfModule>
```

## 4. Database Security

### Database User Permissions

Ensure your database user has only necessary permissions:

**Required permissions:**
- SELECT
- INSERT
- UPDATE
- DELETE
- CREATE (for migrations)
- ALTER (for migrations)
- INDEX (for migrations)
- DROP (for migrations)

**Not needed:**
- SUPER
- FILE
- PROCESS
- RELOAD
- SHUTDOWN
- CREATE USER
- GRANT OPTION

### Database Connection Security

- Use strong, unique database password
- Limit database access to localhost only
- Regular database backups
- Monitor for unusual database activity

## 5. Application Security

### Admin Access Security

**Protect admin routes:**
- Admin panel requires authentication: `/admin`
- Only users with admin role can access
- Session timeout configured (120 minutes)

**Admin user creation:**
```bash
# Create admin user securely
php artisan user:promote your-email@domain.com admin
```

### File Upload Security

**Protected file storage:**
- Files stored in `storage/app/private/`
- Not directly web-accessible
- Download tokens required for access
- Token expiration enforced

### Session Security

**Session configuration:**
- Database-based sessions (more secure than file-based)
- HTTPS-only cookies in production
- Session encryption enabled
- Automatic session cleanup

## 6. Monitoring & Maintenance

### Log Monitoring

**Check these logs regularly:**
- `storage/logs/laravel.log` - Application errors
- cPanel Error Logs - Server errors
- Access logs - Unusual traffic patterns

**Log rotation:**
```bash
# Set up log rotation (if SSH access available)
# Add to crontab:
0 0 * * 0 find /path/to/storage/logs -name "*.log" -mtime +30 -delete
```

### Security Monitoring

**Regular checks:**
- [ ] Monitor failed login attempts
- [ ] Check for unusual file modifications
- [ ] Review access logs for suspicious activity
- [ ] Verify SSL certificate validity
- [ ] Test backup restoration process

### Updates & Patches

**Keep updated:**
- [ ] Laravel framework updates
- [ ] PHP version updates
- [ ] Hosting control panel updates
- [ ] SSL certificate renewals

### Backup Strategy

**Automated backups:**
1. **Database backups:**
   - Daily automated backups via cPanel
   - Test restoration monthly
   - Store backups off-site

2. **File backups:**
   - Weekly full site backups
   - Include uploaded files in `storage/`
   - Exclude cache and temporary files

### Security Testing

**Regular security tests:**
- [ ] Test admin login protection
- [ ] Verify file upload restrictions
- [ ] Check for exposed sensitive files
- [ ] Test SSL configuration
- [ ] Validate form input sanitization

### Incident Response Plan

**If security breach suspected:**
1. **Immediate actions:**
   - Change all passwords (admin, database, email)
   - Review recent access logs
   - Check for unauthorized file modifications
   - Temporarily disable admin access if needed

2. **Investigation:**
   - Analyze logs for entry point
   - Check for malicious files
   - Review user accounts for unauthorized access
   - Document findings

3. **Recovery:**
   - Restore from clean backup if needed
   - Apply security patches
   - Update all credentials
   - Monitor for continued issues

## Security Checklist

### Pre-Launch Security Audit

- [ ] File permissions set correctly
- [ ] .env file protected and configured
- [ ] SSL certificate installed and working
- [ ] Security headers configured
- [ ] Admin access tested and secured
- [ ] Database user permissions limited
- [ ] Backup system configured
- [ ] Monitoring tools set up
- [ ] Error pages don't expose sensitive info
- [ ] Debug mode disabled (`APP_DEBUG=false`)

### Post-Launch Monitoring

- [ ] Daily log review
- [ ] Weekly security scans
- [ ] Monthly backup tests
- [ ] Quarterly security audits
- [ ] Annual penetration testing (if budget allows)

## Emergency Contacts

**Keep these handy:**
- Hosting provider support
- Domain registrar support
- SSL certificate provider
- Database administrator (if separate)
- Security consultant (if applicable)

---

🔒 **Remember:** Security is an ongoing process, not a one-time setup. Regular monitoring and updates are essential for maintaining a secure application.