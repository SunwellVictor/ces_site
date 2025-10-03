<?php
/**
 * Clark English Learning - Production Database Setup Script
 * 
 * This script helps set up the database for production deployment.
 * Run this after uploading files and configuring your .env file.
 * 
 * Usage: php setup_database.php
 */

echo "🚀 Clark English Learning - Database Setup\n";
echo "==========================================\n\n";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    echo "❌ Error: artisan file not found. Please run this script from the application root directory.\n";
    exit(1);
}

// Check if .env file exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found. Please copy .env.example to .env and configure it first.\n";
    exit(1);
}

echo "📋 Running database setup commands...\n\n";

// Commands to run
$commands = [
    'php artisan config:clear' => 'Clearing configuration cache',
    'php artisan cache:clear' => 'Clearing application cache',
    'php artisan migrate --force' => 'Running database migrations',
    'php artisan db:seed --class=RoleSeeder --force' => 'Seeding user roles',
    'php artisan storage:link' => 'Creating storage symlink',
    'php artisan config:cache' => 'Caching configuration',
    'php artisan route:cache' => 'Caching routes',
    'php artisan view:cache' => 'Caching views'
];

$success = true;

foreach ($commands as $command => $description) {
    echo "🔄 {$description}...\n";
    echo "   Running: {$command}\n";
    
    $output = [];
    $return_code = 0;
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        echo "   ✅ Success\n";
        if (!empty($output)) {
            echo "   Output: " . implode("\n           ", $output) . "\n";
        }
    } else {
        echo "   ❌ Failed (Exit code: {$return_code})\n";
        echo "   Error: " . implode("\n          ", $output) . "\n";
        $success = false;
    }
    echo "\n";
}

if ($success) {
    echo "🎉 Database setup completed successfully!\n\n";
    echo "📋 Next Steps:\n";
    echo "1. Create an admin user: php artisan user:promote your-email@domain.com admin\n";
    echo "2. Test your site by visiting your domain\n";
    echo "3. Check the admin panel at /admin\n";
    echo "4. Configure Stripe webhook URL in your Stripe dashboard\n\n";
    echo "🔗 Important URLs:\n";
    echo "   - Main site: https://your-domain.com\n";
    echo "   - Admin panel: https://your-domain.com/admin\n";
    echo "   - Blog: https://your-domain.com/blog\n";
    echo "   - Products: https://your-domain.com/products\n\n";
} else {
    echo "❌ Database setup encountered errors. Please check the output above and resolve any issues.\n\n";
    echo "💡 Common solutions:\n";
    echo "1. Verify database credentials in .env file\n";
    echo "2. Ensure database exists and user has proper permissions\n";
    echo "3. Check file permissions on storage and bootstrap/cache directories\n";
    echo "4. Contact your hosting provider if database connection issues persist\n\n";
}

echo "📝 For troubleshooting, check:\n";
echo "   - storage/logs/laravel.log for application errors\n";
echo "   - Your hosting control panel for database connection details\n";
echo "   - File permissions (storage and bootstrap/cache should be writable)\n\n";