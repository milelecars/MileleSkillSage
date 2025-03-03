<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCandidateTestTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
            // Add new columns here
            $table->timestamp('suspended_at')->nullable()->after('wrong_answers'); 
            $table->text('suspension_reason')->nullable()->after('suspended_at');
            $table->boolean('is_suspended')->default(false)->after('suspension_reason'); 
            $table->integer('unsuspend_count')->default(0)->after('is_suspended'); 
            $table->string('evidence_path')->nullable()->after('suspension_reason');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_test', function (Blueprint $table) {
                
            
                $table->dropColumn('unsuspend_count');
            
                $table->dropColumn('suspended_at');
            
                $table->dropColumn('suspension_reason');
                $table->dropColumn('is_suspended');

                $table->dropColumn('evidence_path');
            
        });
        
    }
}