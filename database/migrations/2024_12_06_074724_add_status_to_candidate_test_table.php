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
            $table->enum('status', ['not started', 'in progress', 'completed', 'accepted', 'rejected'])->default('not started')->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
