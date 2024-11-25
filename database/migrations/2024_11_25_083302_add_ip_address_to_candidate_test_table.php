<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};