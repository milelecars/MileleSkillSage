<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('questions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // You may want to recreate the table here if needed
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->json('options');
            $table->string('correct_answer');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }
};