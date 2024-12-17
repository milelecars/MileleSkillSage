<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->foreignId('flag_type_id')->constrained('flag_types')->onDelete('cascade');
            $table->integer('occurrences');
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->index(['candidate_id', 'test_id']);

            $table->foreign(['candidate_id', 'test_id'])
                  ->references(['candidate_id', 'test_id'])
                  ->on('candidate_test')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_flags');
    }
};