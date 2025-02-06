<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCandidateTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            // Rename the 'score' column to 'correct_answers'
            $table->renameColumn('score', 'correct_answers');

            // Add the 'wrong_answers' column
            $table->integer('wrong_answers')->after('correct_answers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            // Rename 'correct_answers' back to 'score'
            $table->renameColumn('correct_answers', 'score');

            // Drop the 'wrong_answers' column
            $table->dropColumn('wrong_answers');
        });
    }
}
