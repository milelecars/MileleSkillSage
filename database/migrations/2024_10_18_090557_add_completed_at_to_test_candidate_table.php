<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedAtToTestCandidateTable extends Migration
{
    public function up()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('updated_at');
        });
    }

    public function down()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
}