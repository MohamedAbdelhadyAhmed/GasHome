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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // $table->foreignId('available_address_id')->constrained('available_addresses')->cascadeOnDelete();

            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->float('total_price');

            // $table->enum('region', ['makkah', 'madinah', 'jizan'])->default('makkah');
            $table->string('delivery_date')->nullable();
            $table->enum('payment_methode', ['visa','cod'])->default('visa');
            $table->enum('payment_status', ['pending','paid','failed'])->default('pending');
            $table->enum('order_status', ['pending', 'shipped', 'cancelled','delivered'])->default('pending');
            $table->string('payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
