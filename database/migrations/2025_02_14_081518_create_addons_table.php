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
                Schema::create('addons', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
                    $table->foreignId('facility_attribute_id')->nullable()->constrained('facility_attributes')->onDelete('cascade');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->string('name');
                    $table->enum('price_type', ['per_unit', 'flat_rate', 'per_night', 'per_item', 'per_hour']);
                    $table->text('description')->nullable();
                    $table->decimal('base_price', 8, 2);
                    $table->unsignedSmallInteger('capacity')->nullable();
                    $table->unsignedSmallInteger('quantity')->nullable();
                    $table->boolean('is_based_on_quantity')->default(false);
                    $table->boolean('is_available')->default(false);
                    $table->boolean('is_refundable')->default(false);
                    $table->enum('billing_cycle', ['per_day','per_contract'])->default('per_day');
                    $table->enum('show', ['both','staff'])->default('both');
                    $table->softDeletes();
                    $table->timestamps();
                });
            }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
