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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('type')->default('post'); // post, page, lesson
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->json('categories')->nullable(); // array of category names
            $table->json('tags')->nullable(); // array of tag names
            $table->json('metadata')->nullable(); // SEO and additional data
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'type']);
            $table->index(['author_id', 'status']);
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
