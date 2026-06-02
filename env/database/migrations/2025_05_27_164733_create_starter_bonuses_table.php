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
       Schema::create('starter_bonuses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The one receiving the bonus
    $table->foreignId('referral_id')->constrained('users')->onDelete('cascade'); // The one that triggered the bonus
    $table->integer('generation'); // 1, 2, 3, or 4
    $table->decimal('amount', 10, 2);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_bonuses');
    }
};
