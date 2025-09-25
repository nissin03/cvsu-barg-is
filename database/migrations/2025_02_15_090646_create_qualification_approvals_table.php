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
        Schema::create('qualification_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained('availabilities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('qualification')->nullable();
            $table->enum('status', ['pending', 'approved', 'canceled',])->default('pending');
            $table->string('canceled_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualification_approvals');
    }
};
