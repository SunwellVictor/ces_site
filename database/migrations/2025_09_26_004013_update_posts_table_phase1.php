<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop indexes first (using proper index names)
            $table->dropIndex('posts_status_type_index');
            $table->dropIndex('posts_is_featured_index');
            
            // Remove columns not in Phase 1 spec (only existing ones)
            $table->dropColumn([
                'type',
                'categories',
                'tags',
                'metadata',
                'views_count',
                'is_featured',
                'allow_comments'
            ]);
            
            // Add new columns for Phase 1 spec
            $table->longText('body')->after('excerpt');
            $table->string('seo_title')->nullable()->after('published_at');
            $table->string('seo_description')->nullable()->after('seo_title');
            
            // Update status column to use enum
            $table->enum('status', ['draft', 'published'])->default('draft')->change();
            
            // Note: indexes for author_id+status and published_at already exist from original migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop Phase 1 indexes
            $table->dropIndex(['author_id', 'status']);
            $table->dropIndex(['posts_published_at_index']);
            
            // Remove Phase 1 columns
            $table->dropColumn(['body', 'seo_title', 'seo_description']);
            
            // Restore previously removed columns (only ones that existed)
            $table->string('type')->default('post')->after('status');
            $table->json('categories')->nullable()->after('author_id');
            $table->json('tags')->nullable()->after('categories');
            $table->json('metadata')->nullable()->after('tags');
            $table->unsignedInteger('views_count')->default(0)->after('metadata');
            $table->boolean('is_featured')->default(false)->after('views_count');
            $table->boolean('allow_comments')->default(true)->after('is_featured');
            
            // Revert status column
            $table->string('status')->default('draft')->change();
        });
    }
};
