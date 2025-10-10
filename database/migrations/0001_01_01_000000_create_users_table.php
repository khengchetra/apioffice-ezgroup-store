<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->integer('is_show')->default(1);
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        // Gender
        Schema::create('gender', function (Blueprint $table) {
            $table->id();
            $table->string('gender_name');
            $table->integer('is_show')->default(1);
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        // Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->integer('is_show')->default(1);
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // creator
            $table->timestamps();
        });

        // Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
            $table->integer('is_show')->default(1);
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // creator
            $table->timestamps();
        });

        // Positions
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name');
            $table->integer('is_show')->default(1);
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // creator
            $table->timestamps();
        });

        // Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('number_phone')->nullable();
            $table->text('profile_image')->nullable();
            $table->text('cover_image')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('start_work')->nullable();
            $table->string('employee_code');
            $table->string('bank_account_number')->nullable();
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('role_id');
            $table->integer('is_show')->default(1);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // self reference
            $table->text('other')->nullable();
            $table->timestamps();
        });

        // Permission core tables (no FKs yet)
        Schema::create('permission', function (Blueprint $table) {
            $table->id();
            $table->string('permission_name');
            $table->text('remark')->nullable();
            $table->integer('is_show')->default(1);
            $table->timestamps();
        });

        Schema::create('sub_permission', function (Blueprint $table) {
            $table->id();
            $table->string('sub_permission_name');
            $table->text('remark')->nullable();
            $table->integer('is_show')->default(1);
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->timestamps();
        });

        Schema::create('check_permission', function (Blueprint $table) {
            $table->id();
            $table->string('check_permission_name');
            $table->text('remark')->nullable();
            $table->integer('is_show')->default(1);
            $table->unsignedBigInteger('sub_permission_id')->nullable();
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->timestamps();
        });

        Schema::create('compile_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->unsignedBigInteger('sub_permission_id')->nullable();
            $table->unsignedBigInteger('check_permission_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compile_permission');
        Schema::dropIfExists('check_permission');
        Schema::dropIfExists('sub_permission');
        Schema::dropIfExists('permission');
        Schema::dropIfExists('users');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('gender');
        Schema::dropIfExists('roles');
    }
};