<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::rename('question_choices', 'choices');
        Schema::rename('question_media', 'media');
        Schema::rename('flag_types', 'types');
        Schema::rename('candidate_test_screenshots', 'screenshots');
        Schema::rename('candidate_flags', 'flags');
    }
    
    public function down()
    {
        Schema::rename('choices', 'question_choices');
        Schema::rename('media', 'question_media');
        Schema::rename('types', 'flag_types');
        Schema::rename('screenshots', 'candidate_test_screenshots');
        Schema::rename('flags', 'candidate_flags');
    }
};