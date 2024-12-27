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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('street_name');
            // $table->string('description')->nullable();
            $table->string('building_number')->nullable();
            // $table->string('building_name')->nullable();
            // $table->string('neighborhood')->nullable();
            // $table->string('nearest_landmark')->nullable();
            $table->string('city');
            $table->string('state'); // المحافظة
            $table->enum('status', allowed: ['home', 'other'])->default('other');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
