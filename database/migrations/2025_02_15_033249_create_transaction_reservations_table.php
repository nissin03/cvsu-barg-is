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
        Schema::create('transaction_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained('availabilities')->onDelete('cascade');
            $table->foreignId('facility_attribute_id')->nullable()->constrained('facility_attributes')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('cascade');
            $table->foreignId('price_id')->constrained('prices')->onDelete('cascade');
            $table->foreignId('addon_id')->nullable()->constrained('addons')->onDelete('cascade');
            $table->foreignId('addon_reservation_id')->nullable()->constrained('addons_reservations')->onDelete('cascade');
            $table->foreignId('addon_payment_id')->nullable()->constrained('addon_payments')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['reserved', 'canceled', 'completed', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_reservations');
    }
};
