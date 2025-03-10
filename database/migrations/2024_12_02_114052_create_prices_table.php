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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->string('name')->nullable()->default('Price');
            $table->decimal('value', 8, 2);
            $table->enum('price_type', ['individual', 'whole']);
           
            $table->boolean('is_based_on_days')->default(false);
            $table->boolean('is_there_a_quantity')->default(false);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
