<?php

namespace App\Console\Commands;

use App\Models\DownloadGrant;
use App\Models\File;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateFakeDownloadGrants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:fake 
                            {--count=10 : Number of grants to create}
                            {--user= : Specific user ID to create grants for}
                            {--expired : Create some expired grants}
                            {--used : Create some fully used grants}
                            {--clear : Clear existing fake grants first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake download grants for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $userId = $this->option('user');
        $includeExpired = $this->option('expired');
        $includeUsed = $this->option('used');
        $clear = $this->option('clear');

        if ($clear) {
            $this->info('Clearing existing fake grants...');
            DownloadGrant::whereNull('order_id')->delete();
            $this->info('Cleared existing fake grants.');
        }

        // Check if we have the required data
        $users = User::all();
        $products = Product::with('files')->get();
        $files = File::all();

        if ($users->isEmpty()) {
            $this->error('No users found. Please create some users first.');
            return 1;
        }

        if ($products->isEmpty() || $files->isEmpty()) {
            $this->error('No products or files found. Please create some products and files first.');
            return 1;
        }

        $this->info("Generating {$count} fake download grants...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        DB::transaction(function () use ($count, $userId, $includeExpired, $includeUsed, $users, $products, $files, $bar) {
            for ($i = 0; $i < $count; $i++) {
                $user = $userId ? User::find($userId) : $users->random();
                $product = $products->random();
                $file = $product->files->isNotEmpty() ? $product->files->random() : $files->random();

                $maxDownloads = rand(1, 10);
                $downloadsUsed = 0;
                $expiresAt = now()->addDays(rand(7, 90));

                // Create some expired grants if requested
                if ($includeExpired && rand(1, 4) === 1) {
                    $expiresAt = now()->subDays(rand(1, 30));
                }

                // Create some fully used grants if requested
                if ($includeUsed && rand(1, 4) === 1) {
                    $downloadsUsed = $maxDownloads;
                }

                DownloadGrant::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'file_id' => $file->id,
                    'order_id' => null, // Fake grants don't have orders
                    'max_downloads' => $maxDownloads,
                    'downloads_used' => $downloadsUsed,
                    'expires_at' => $expiresAt,
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Show summary
        $totalGrants = DownloadGrant::count();
        $validGrants = DownloadGrant::whereNull('order_id')
            ->where('expires_at', '>', now())
            ->whereColumn('downloads_used', '<', 'max_downloads')
            ->count();
        $expiredGrants = DownloadGrant::whereNull('order_id')
            ->where('expires_at', '<=', now())
            ->count();
        $usedUpGrants = DownloadGrant::whereNull('order_id')
            ->whereColumn('downloads_used', '>=', 'max_downloads')
            ->count();

        $this->info("✅ Successfully created {$count} fake download grants!");
        $this->table(
            ['Type', 'Count'],
            [
                ['Total Grants', $totalGrants],
                ['Valid Grants', $validGrants],
                ['Expired Grants', $expiredGrants],
                ['Used Up Grants', $usedUpGrants],
            ]
        );

        $this->info('💡 You can now test the download system with these grants.');
        $this->info('💡 Use "php artisan grant:fake --clear" to remove fake grants.');

        return 0;
    }
}
