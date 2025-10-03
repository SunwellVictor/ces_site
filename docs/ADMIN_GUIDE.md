# Admin Guide

This guide covers the administration features of the CES site, including user role management and admin access.

## Overview

The admin system provides role-based access control with the following roles:
- **Admin**: Full system access
- **Editor**: Content management access
- **Teacher**: Educational content access
- **Customer**: Standard user access

## Getting Started

### 1. Setting Up Roles

First, ensure the roles are seeded in your database:

```bash
php artisan db:seed --class=RoleSeeder
```

This creates the following roles:
- `admin` - Full administrative access
- `editor` - Content management permissions
- `teacher` - Educational content permissions
- `customer` - Basic user permissions (default)

### 2. Promoting Users to Admin

To promote a user to admin role, use the built-in Artisan command:

```bash
php artisan user:promote {email} {role}
```

**Examples:**

```bash
# Promote user to admin
php artisan user:promote admin@example.com admin

# Promote user to editor
php artisan user:promote editor@example.com editor

# Promote user to teacher
php artisan user:promote teacher@example.com teacher
```

**Notes:**
- The command is idempotent - running it multiple times won't create duplicate role assignments
- The user must exist in the database before promotion
- The role must exist in the database before assignment

### 3. Accessing the Admin Panel

Once a user has admin role:

1. Log in to the site normally
2. Navigate to `/admin` or use the admin link in the navigation
3. The admin dashboard provides:
   - System metrics overview
   - Recent orders and users
   - Quick action links to manage different sections

## Admin Features

### Dashboard
- **URL**: `/admin`
- **Features**: 
  - User, product, order, and post counts
  - Recent activity overview
  - Quick navigation to management sections

### Access Control
- Admin routes are protected by the `admin` middleware
- Non-admin users receive a 403 Forbidden response
- Guests are redirected to login

### Role Management
- Roles are managed through the database seeder
- User role assignments use the promotion command
- Role checking is done via the `hasRole()` method on User model

## Development

### Running Tests

The admin system includes comprehensive tests:

```bash
# Run all tests
php artisan test

# Run specific admin tests
php artisan test tests/Feature/AdminAccessTest.php
php artisan test tests/Unit/PromoteUserCommandTest.php
```

### Adding New Admin Features

1. Create controllers in `app/Http/Controllers/Admin/`
2. Add routes to the admin group in `routes/web.php`
3. Ensure proper middleware protection
4. Create corresponding views in `resources/views/admin/`

### Policies

Policy stubs are registered in `AuthServiceProvider.php`:
- `PostPolicy`
- `ProductPolicy` 
- `OrderPolicy`
- `UserPolicy`

Implement these policies as needed for fine-grained permissions.

## Troubleshooting

### Common Issues

**"Role not found" errors:**
- Ensure roles are seeded: `php artisan db:seed --class=RoleSeeder`
- Check role slugs match exactly: `admin`, `editor`, `teacher`, `customer`

**"User not found" errors:**
- Verify the email address is correct
- Ensure the user has registered/been created

**403 Access Denied:**
- Confirm the user has the correct role assigned
- Check that the admin middleware is working
- Verify role assignment with: `php artisan tinker` then `User::where('email', 'user@example.com')->first()->roles`

**Admin dashboard errors:**
- Ensure all required models exist (User, Product, Order, Post, File)
- Check database migrations are up to date: `php artisan migrate`

## Security Notes

- Admin access is strictly controlled through middleware
- Role assignments are permanent until manually changed
- Always use the promotion command rather than direct database manipulation
- Regular users cannot access admin routes even if they guess the URLs