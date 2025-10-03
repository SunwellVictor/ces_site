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
        Schema::create('stripe_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // Stripe event ID (e.g., evt_1234...)
            $table->string('event_type'); // Event type (e.g., checkout.session.completed)
            $table->timestamp('processed_at'); // When we processed this event
            $table->json('event_data')->nullable(); // Store the full event data for debugging
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['event_type', 'processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_events');
    }
};
