<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
      {
            Schema::table('payments', function (Blueprint $table) {
                  $table->unsignedBigInteger('discount_id')->nullable()->after('total_price');
                  $table->decimal('gross_total', 12, 2)->nullable()->after('total_price');
                  $table->decimal('discount_percent', 5, 2)->nullable()->after('gross_total');
                  $table->decimal('discount_amount', 12, 2)->nullable()->after('discount_percent');
                  $table->string('discount_applies_to')->nullable()->after('discount_amount');
                  $table->string('discount_proof_path')->nullable()->after('discount_applies_to');
            });
      }

      public function down(): void
      {
            Schema::table('payments', function (Blueprint $table) {
                  $table->dropColumn([
                        'discount_id',
                        'gross_total',
                        'discount_percent',
                        'discount_amount',
                        'discount_applies_to',
                        'discount_proof_path',
                  ]);
            });
      }
};
