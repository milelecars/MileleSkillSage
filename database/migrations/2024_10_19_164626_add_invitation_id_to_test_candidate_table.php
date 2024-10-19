<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvitationIdToTestCandidateTable extends Migration
{
    public function up()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->unsignedBigInteger('invitation_id')->nullable();
            $table->foreign('invitation_id')->references('id')->on('test_invitations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('test_candidate', function (Blueprint $table) {
            $table->dropForeign(['invitation_id']);
            $table->dropColumn('invitation_id');
        });
    }
}