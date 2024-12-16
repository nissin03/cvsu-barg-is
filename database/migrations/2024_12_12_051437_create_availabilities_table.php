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
            $table->foreignId('price_id')->constrained('prices')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('remaining_capacity')->default(0);
            $table->string('qualification')->nullable();
            $table->enum('status', ['reserved', 'canceled', 'completed', 'pending'])->default('pending');
            $table->date('date_from');
            $table->date('date_to');
            // $table->string('time_slot');
            $table->decimal('total_price', 10, 2)->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
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
