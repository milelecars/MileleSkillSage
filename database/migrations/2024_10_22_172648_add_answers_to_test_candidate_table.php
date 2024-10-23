<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnswersToTestCandidateTable extends Migration
{
    public function up()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->json('answers')->nullable();
        });
    }

    public function down()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->dropColumn('answers');
        });
    }
}
