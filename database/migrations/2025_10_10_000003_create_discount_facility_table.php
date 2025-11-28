<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
      {
            Schema::create('discount_facility', function (Blueprint $table) {
                  $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
                  $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
                  $table->timestamps();

                  $table->primary(['facility_id', 'discount_id']);
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('discount_facility');
      }
};
