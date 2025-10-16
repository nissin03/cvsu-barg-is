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
        Schema::create('addon_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_reservation_id')->nullable()->constrained('transaction_reservations')->cascadeOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained('addons')->cascadeOnDelete();
            $table->foreignId('addon_reservation_id')->nullable()->constrained('addons_reservations')->cascadeOnDelete();
            $table->foreignId('addon_payment_id')->nullable()->constrained('addon_payments')->cascadeOnDelete();
            $table->enum('status', ['unpaid', 'paid','forfeit', 'refunded'])->default('unpaid');
            $table->timestamps();
            $table->unique(['addon_payment_id']);
             $table->unique(['transaction_reservation_id', 'addon_id', 'addon_reservation_id'], 'trx_addon_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_transactions');
    }
};
