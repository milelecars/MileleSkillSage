<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Add department_id to candidate_test table
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('role');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Rollback foreign key and column first
        Schema::table('candidate_test', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        // Drop departments table
        Schema::dropIfExists('departments');
    }
};

