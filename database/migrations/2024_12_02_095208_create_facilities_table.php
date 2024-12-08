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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('facility_type', ['individual', 'whole_place', 'both']);
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('rules_and_regulations'); 
            $table->string('requirements');
            $table->boolean('featured')->default(false);
            $table->string('image')->nullable();
            $table->text('images')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('archived')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
