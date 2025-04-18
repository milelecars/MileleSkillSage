<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_test_screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->string('screenshot_path');
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
        Schema::dropIfExists('candidate_test_screenshots');
    }
};