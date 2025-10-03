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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path'); // e.g., downloads/pack-001.zip
            $table->string('original_name');
            $table->bigInteger('size_bytes');
            $table->string('checksum')->nullable(); // sha256
            $table->timestamps();
            
            $table->index('disk');
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
