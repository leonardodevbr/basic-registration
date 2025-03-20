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
        Schema::create('benefit_deliveries', function (Blueprint $table) {
            $table->id();
            // Foreign keys for the benefit and the person
            $table->foreignId('benefit_id')->constrained('benefits');
            $table->foreignId('person_id')->constrained('people');

            // New columns for password issuance and expiration
            $table->string('ticket_code')->unique(); // Code generated at registration
            $table->dateTime('valid_until'); // Expiration date/time for the code

            // Status of the code (e.g., PENDING, DELIVERED, EXPIRED)
            $table->string('status')->default('PENDING');

            // Optional foreign keys to track which user registered and delivered the benefit
            $table->foreignId('registered_by_id')->nullable()->constrained('users');
            $table->foreignId('delivered_by_id')->nullable()->constrained('users');

            // Foreign key for the unit (where the benefit is registered or delivered)
            $table->foreignId('unit_id')->nullable()->constrained('units');

            // Date and time when the benefit was delivered (nullable until delivered)
            $table->dateTime('delivered_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefit_deliveries');
    }
};
