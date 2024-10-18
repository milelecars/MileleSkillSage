<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDurationToTestsTable extends Migration
{
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->integer('duration')->nullable();
        });

        // Set default value for existing records
        DB::table('tests')->update(['duration' => 20]);

        // Now make the column NOT NULL
        Schema::table('tests', function (Blueprint $table) {
            $table->integer('duration')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
}