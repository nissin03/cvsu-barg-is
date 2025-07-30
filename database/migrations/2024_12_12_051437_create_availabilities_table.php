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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->foreignId('facility_attribute_id')->nullable()->constrained('facility_attributes')->onDelete('cascade');
            $table->integer('remaining_capacity')->default(0);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->time('time_start')->nullable(); 
            $table->time('time_end')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
