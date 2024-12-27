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
        Schema::create('available_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->json('coordinates');
            


            // $table->enum('region_ar', ['Mecca', 'Medina', 'Riyadh'])->default('makkah');
            // $table->enum('region_en', ['مكة', 'المدينة', 'الرياض'])->default('makkah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_addresses');
    }
};
