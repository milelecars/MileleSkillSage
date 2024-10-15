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
        // Drop candidate_responses first
        Schema::dropIfExists('candidate_responses');
        
        // Then drop user_responses
        Schema::dropIfExists('user_responses');
        
        // Finally drop questions
        Schema::dropIfExists('questions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You can recreate the tables here if needed.
        // Ensure you recreate them in the same order with proper structure.
    }
};
