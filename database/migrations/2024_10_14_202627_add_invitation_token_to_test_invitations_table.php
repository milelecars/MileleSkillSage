<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvitationTokenToTestInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_invitations', function (Blueprint $table) {
            $table->string('invitation_token')->nullable()->after('invitation_link'); // Allow NULL values
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_invitations', function (Blueprint $table) {
            $table->dropColumn('invitation_token'); // Dropping the column on rollback
        });
    }
}
