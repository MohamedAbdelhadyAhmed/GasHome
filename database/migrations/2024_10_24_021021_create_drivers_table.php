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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->string('image')->nullable();
            $table->string('password');
            $table->string('license_number');
            $table->string('vehicle_license');
            $table->string('vehicle_number');
            $table->string('code')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('address');
            // $table->string('state'); // المحافظة
            // $table->string('city');
            // $table->string('street_name');
            // $table->string('building_number')->nullable();
            // $table->string('building_name')->nullable();
            // end address
            // $table->string('area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
