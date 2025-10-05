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
        Schema::create('addon_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained('addons')->onDelete('cascade');
            $table->foreignId('addon_reservation_id')->constrained('addons_reservations')->onDelete('cascade');
            $table->decimal('total', 8,2);
            $table->enum('status', ['unpaid', 'paid','not applicable','forfeit', 'refunded'])->default('not applicable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_payments');
    }
};
