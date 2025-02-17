<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('category')->nullable()->after('question_type');
            $table->boolean('reverse')->nullable()->after('category');
            $table->boolean('red_flag')->nullable()->after('reverse');
        });

    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['category', 'reverse', 'red_flag']);
        });
    }
};
