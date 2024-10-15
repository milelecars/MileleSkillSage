<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestCandidateTable extends Migration // Renamed class
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_candidate', function (Blueprint $table) { // Renamed table
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->foreignId('candidate_id') // Changed user_id to candidate_id
                  ->constrained('candidates') // Reference candidates table
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_candidate'); // Renamed here
    }
};
