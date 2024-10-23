<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->json('monitoring_data')->nullable()->after('score')->comment('Stores all test monitoring data including suspicious activities');
        });
    }

    public function down()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->dropColumn('monitoring_data');
        });
    }
};