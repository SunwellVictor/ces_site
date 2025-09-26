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
        Schema::table('download_grants', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreignId('order_id')->nullable()->change()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('download_grants', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreignId('order_id')->nullable(false)->change()->constrained()->onDelete('cascade');
        });
    }
};
