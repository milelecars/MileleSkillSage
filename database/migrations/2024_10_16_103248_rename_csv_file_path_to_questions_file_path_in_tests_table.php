<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCsvFilePathToQuestionsFilePathInTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            // Rename the column from 'questions_file' to 'questions_file_path'
            $table->renameColumn('questions_file', 'questions_file_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            // Revert the column name change
            $table->renameColumn('questions_file_path', 'questions_file');
        });
    }
}
