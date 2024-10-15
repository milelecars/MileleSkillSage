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
        Schema::create('candidate_responses', function (Blueprint $table) { // Renamed table
            $table->id();
            $table->foreignId('candidate_id') // Updated to candidate_id
                  ->constrained('candidates') // Change to reference candidates table
                  ->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('user_answer');
            $table->boolean('is_correct');
            $table->timestamp('answered_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_responses'); // Renamed here
    }
};
