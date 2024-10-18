<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartedAtToTestCandidateTable extends Migration
{
    public function up()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
}