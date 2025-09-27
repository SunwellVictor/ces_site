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
                            {email : User email to grant access to}
                            {product_slug : Product slug to grant access to}
                            {--downloads=5 : Number of downloads allowed}
                            {--expires="+2 years" : Expiration time (relative format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant fake download access to a user for a specific product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $productSlug = $this->argument('product_slug');
        $downloads = (int) $this->option('downloads');
        $expires = $this->option('expires');

        // Find the user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Find the product
        $product = Product::where('slug', $productSlug)->first();
        if (!$product) {
            $this->error("Product with slug '{$productSlug}' not found.");
            return 1;
        }

        // Get product files
        $files = $product->files;
        if ($files->isEmpty()) {
            $this->error("Product '{$productSlug}' has no files attached.");
            return 1;
        }

        // Parse expiration date
        $expiresAt = null;
        if ($expires) {
            try {
                $expiresAt = new \DateTime($expires);
            } catch (\Exception $e) {
                $this->error("Invalid expiration format: {$expires}");
                return 1;
            }
        }

        $this->info("Creating download grants for user '{$email}' and product '{$productSlug}'...");

        $createdCount = 0;

        DB::transaction(function () use ($user, $product, $files, $downloads, $expiresAt, &$createdCount) {
            foreach ($files as $file) {
                // Check if grant already exists for this user/product/file combination
                $existingGrant = DownloadGrant::where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->where('file_id', $file->id)
                    ->whereNull('order_id') // Only check fake grants
                    ->first();

                if ($existingGrant) {
                    $this->warn("Grant already exists for file: {$file->filename}");
                    continue;
                }

                DownloadGrant::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'file_id' => $file->id,
                    'order_id' => null, // Fake grants don't have orders
                    'max_downloads' => $downloads,
                    'downloads_used' => 0,
                    'expires_at' => $expiresAt,
                ]);

                $createdCount++;
                $this->info("✓ Created grant for file: {$file->filename}");
            }
        });

        if ($createdCount > 0) {
            $this->info("✅ Successfully created {$createdCount} download grant(s) for user '{$email}' and product '{$productSlug}'!");
            $this->info("📥 Downloads allowed: {$downloads}");
            if ($expiresAt) {
                $this->info("⏰ Expires: {$expiresAt->format('Y-m-d H:i:s')}");
            } else {
                $this->info("⏰ No expiration set");
            }
        } else {
            $this->warn("No new grants were created (all grants already exist).");
        }

        return 0;
    }
}
