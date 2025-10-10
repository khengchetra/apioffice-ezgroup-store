<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Users FKs
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('gender_id')->references('id')->on('gender')->onDelete('set null');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('sub_permission', function (Blueprint $table) {
            $table->foreign('permission_id')->references('id')->on('permission')->onDelete('cascade');
        });

        Schema::table('check_permission', function (Blueprint $table) {
            $table->foreign('sub_permission_id')->references('id')->on('sub_permission')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permission')->onDelete('cascade');
        });

        Schema::table('compile_permission', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permission')->onDelete('cascade');
            $table->foreign('sub_permission_id')->references('id')->on('sub_permission')->onDelete('cascade');
            $table->foreign('check_permission_id')->references('id')->on('check_permission')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);
            $table->dropForeign(['role_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('permission', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::table('sub_permission', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
        });

        Schema::table('check_permission', function (Blueprint $table) {
            $table->dropForeign(['sub_permission_id']);
            $table->dropForeign(['permission_id']);
        });

        Schema::table('compile_permission', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
            $table->dropForeign(['sub_permission_id']);
            $table->dropForeign(['check_permission_id']);
        });
    }
};