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
        Schema::create('addons_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained('addons')->onDelete('cascade');
            $table->foreignId('availability_id')->nullable()->constrained('availabilities')->onDelete('cascade');
            $table->unsignedSmallInteger('remaining_quantity')->nullable();
            $table->unsignedSmallInteger('remaining_capacity')->nullable();
            $table->unsignedSmallInteger('nights')->nullable();
           
           

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons_reservations');
    }
};
