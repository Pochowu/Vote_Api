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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');

            // Champs spécifiques au vote
            $table->string('amount');
            $table->string('voting_name')->nullable();
            $table->integer('votes_number')->default(1);
            $table->enum('payment_method', ['mix_by_yas', 'flooz']);
            $table->string('phone_number')->required();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
