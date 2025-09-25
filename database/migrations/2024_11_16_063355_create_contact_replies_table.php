<?php

use App\Models\User;
use App\Models\Contact;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Contact::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'admin_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('admin_reply');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_replies');
    }
};
