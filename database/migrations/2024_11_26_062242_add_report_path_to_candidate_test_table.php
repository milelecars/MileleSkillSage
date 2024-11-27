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
            $table->string('report_path')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function dropDown(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
           $table->dropDown('report_path');
        });
    }
};
