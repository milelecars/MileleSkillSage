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

        // Step 5 (optional): Add default back using driver-specific SQL
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE candidate_test ALTER COLUMN status SET DEFAULT 'not started'");
        } elseif ($driver === 'mysql') {
            // On MySQL, ALTER COLUMN ... SET DEFAULT is supported on 8.0.13+ for non-ENUM,
            // but since this is an ENUM we use MODIFY to re-declare the enum with a default.
            DB::statement("ALTER TABLE candidate_test MODIFY status ENUM('not started','in progress','suspended','completed','accepted','rejected','expired') DEFAULT 'not started'");
        } else {
            // SQLite and other drivers: skip setting default to avoid unsupported ALTERs.

        }
        } elseif ($driver === 'mysql') {
            // On MySQL, ALTER COLUMN ... SET DEFAULT is supported on 8.0.13+ for non-ENUM,
            // but since this is an ENUM we use MODIFY to re-declare the enum with a default.
            DB::statement("ALTER TABLE candidate_test MODIFY status ENUM('not started','in progress','suspended','completed','accepted','rejected','expired') DEFAULT 'not started'");
        } else {
            // SQLite and other drivers: skip setting default to avoid unsupported ALTERs.
        }
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

        // Step 5 (optional): Restore default with driver-specific SQL
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE candidate_test ALTER COLUMN status SET DEFAULT 'not started'");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE candidate_test MODIFY status ENUM('not started','in progress','suspended','completed','accepted','rejected') DEFAULT 'not started'");
        } else {
            // SQLite and other drivers: skip
        }
    }
};
