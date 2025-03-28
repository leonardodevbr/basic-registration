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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('name');
            $table->string('cpf', 14)->unique();
            $table->string('phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('nis')->nullable();
            $table->string('rg')->nullable();
            $table->string('issuing_agency')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('race_color')->nullable();
            $table->string('nationality')->nullable();
            $table->string('naturalness')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('thumb_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
