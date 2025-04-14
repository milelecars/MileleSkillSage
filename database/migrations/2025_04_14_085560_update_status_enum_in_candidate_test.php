<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Create a temporary column WITHOUT default (to avoid SQL issues during rename)
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->enum('status_new', ['not started', 'in progress', 'suspended', 'completed', 'accepted', 'rejected', 'expired'])
                  ->nullable()
                  ->after('ip_address');
        });

        // Step 2: Copy data from old column to new
        DB::statement('UPDATE candidate_test SET status_new = status');

        // Step 3: Drop old column
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Step 4: Rename new column to original name
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // Step 5 (optional): Add default back using raw SQL
        DB::statement("ALTER TABLE candidate_test ALTER COLUMN status SET DEFAULT 'not started'");
    }

    public function down()
    {
        // Step 1: Create backup column without 'expired'
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->enum('status_old', ['not started', 'in progress', 'suspended', 'completed', 'accepted', 'rejected'])
                  ->nullable()
                  ->after('ip_address');
        });

        // Step 2: Copy data back excluding 'expired'
        DB::statement("UPDATE candidate_test SET status_old = status WHERE status != 'expired'");

        // Step 3: Drop current 'status' column
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Step 4: Rename old back to 'status'
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });

        // Step 5 (optional): Restore default
        DB::statement("ALTER TABLE candidate_test ALTER COLUMN status SET DEFAULT 'not started'");
    }
};
