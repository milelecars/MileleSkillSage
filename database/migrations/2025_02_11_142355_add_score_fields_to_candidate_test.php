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
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->integer('score')->nullable()->after('completed_at');
            $table->json('red_flags')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropColumn('score');
            $table->dropColumn('red_flags');
        });
    }
};
