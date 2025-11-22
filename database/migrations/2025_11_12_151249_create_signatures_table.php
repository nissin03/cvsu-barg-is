<?php

use App\Models\Category;
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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->enum('category', [
                'facility',
                'product'
            ]);
            $table->enum('report_type', [
                'sales',
                'product',
                'inventory',
                'users',
                'all'
            ]);
            $table->string('label');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedMediumInteger('order_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
