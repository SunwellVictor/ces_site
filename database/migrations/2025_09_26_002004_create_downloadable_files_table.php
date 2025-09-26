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
        Schema::create('downloadable_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_type'); // pdf, mp3, mp4, zip, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->text('description')->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('download_limit')->nullable(); // null = unlimited
            $table->integer('download_count')->default(0);
            $table->json('metadata')->nullable(); // additional file info
            $table->timestamps();
            
            $table->index(['product_id', 'is_active']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloadable_files');
    }
};
