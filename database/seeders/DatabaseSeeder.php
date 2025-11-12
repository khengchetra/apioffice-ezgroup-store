<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        DB::table('roles')->insert([
            [
                'role_name' => 'Admin',
                'is_show' => 1,
                'remark' => 'System administrator with full access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Gender
        DB::table('gender')->insert([
            [
                'gender_name' => 'Male',
                'is_show' => 1,
                'remark' => 'Male gender',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gender_name' => 'Female',
                'is_show' => 1,
                'remark' => 'Female gender',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Branches
        DB::table('branches')->insert([
            [
                'branch_name' => 'Head Office',
                'is_show' => 1,
                'remark' => 'Main branch in Head Office',
                'user_id' => null, // Will be updated later
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Departments
        DB::table('departments')->insert([
            [
                'department_name' => 'IT',
                'is_show' => 1,
                'remark' => 'Information Technology Department',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Positions
        DB::table('positions')->insert([
            [
                'position_name' => 'Web Developer',
                'is_show' => 1,
                'remark' => 'Develops web solutions',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Users
        DB::table('users')->insert([
            [
                'first_name' => 'Kheng',
                'last_name' => 'Chetra',
                'number_phone' => '012345678',
                'profile_image' => null,
                'cover_image' => null,
                'date_of_birth' => '1990-05-15',
                'start_work' => '2023-01-01',
                'employee_code' => 'EMP001',
                'bank_account_number' => '1234567890',
                'username' => 'admin',
                'email' => 'kheng.chetra.belly@gmail.com',
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(60),
                'gender_id' => 1, // Male
                'role_id' => 1, // Admin
                'is_show' => 1,
                'branch_id' => 1, // Head Office
                'department_id' => 1, // IT
                'position_id' => 1, // Web Developer
                'user_id' => null, // Self reference, will be updated
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Update user_id for branches, departments, and positions
        DB::table('branches')->where('id', 1)->update(['user_id' => 1]);
        DB::table('departments')->where('id', 1)->update(['user_id' => 1]);
        DB::table('positions')->where('id', 1)->update(['user_id' => 1]);

        // Permissions
        DB::table('permission')->insert([
            [
                'permission_name' => 'setting',
                'remark' => 'setting',
                'is_show' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Sub Permissions
        DB::table('sub_permission')->insert([
            [
                'sub_permission_name' => 'User Management',
                'remark' => 'User Management',
                'is_show' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_permission_name' => 'Role Managment',
                'remark' => 'Role Managment',
                'is_show' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_permission_name' => 'Branch',
                'remark' => 'Branch',
                'is_show' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_permission_name' => 'Department',
                'remark' => 'Department',
                'is_show' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_permission_name' => 'Position',
                'remark' => 'Position',
                'is_show' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Check Permissions
        DB::table('check_permission')->insert([
            [
                'check_permission_name' => 'view',
                'remark' => 'view user',
                'is_show' => 1,
                'sub_permission_id' => 1,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'create',
                'remark' => 'create user',
                'is_show' => 1,
                'sub_permission_id' => 1,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'update',
                'remark' => 'update user',
                'is_show' => 1,
                'sub_permission_id' => 1,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'delete',
                'remark' => 'delete user',
                'is_show' => 1,
                'sub_permission_id' => 1,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'view',
                'remark' => 'view role',
                'is_show' => 1,
                'sub_permission_id' => 2,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'create',
                'remark' => 'create role',
                'is_show' => 1,
                'sub_permission_id' => 2,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'update',
                'remark' => 'update role',
                'is_show' => 1,
                'sub_permission_id' => 2,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'delete',
                'remark' => 'delete role',
                'is_show' => 1,
                'sub_permission_id' => 2,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'view',
                'remark' => 'view Branch',
                'is_show' => 1,
                'sub_permission_id' => 3,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'create',
                'remark' => 'create  Branch',
                'is_show' => 1,
                'sub_permission_id' => 3,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'update',
                'remark' => 'update  Branch',
                'is_show' => 1,
                'sub_permission_id' => 3,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'delete',
                'remark' => 'delete Branch',
                'is_show' => 1,
                'sub_permission_id' => 3,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'view',
                'remark' => 'view Department',
                'is_show' => 1,
                'sub_permission_id' => 4,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'create',
                'remark' => 'create Department',
                'is_show' => 1,
                'sub_permission_id' => 4,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'update',
                'remark' => 'update Department',
                'is_show' => 1,
                'sub_permission_id' => 4,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'delete',
                'remark' => 'delete Department',
                'is_show' => 1,
                'sub_permission_id' => 4,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'view',
                'remark' => 'view Position',
                'is_show' => 1,
                'sub_permission_id' => 5,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'create',
                'remark' => 'create Position',
                'is_show' => 1,
                'sub_permission_id' => 5,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'update',
                'remark' => 'update Position',
                'is_show' => 1,
                'sub_permission_id' => 5,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'check_permission_name' => 'delete',
                'remark' => 'delete Position',
                'is_show' => 1,
                'sub_permission_id' => 5,
                'permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Compile Permissions
        DB::table('compile_permission')->insert([
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 2,
                'check_permission_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 2,
                'check_permission_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 1,
                'check_permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 1,
                'check_permission_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 1,
                'check_permission_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 1,
                'check_permission_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 2,
                'check_permission_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 2,
                'check_permission_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 3,
                'check_permission_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 5,
                'check_permission_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 5,
                'check_permission_id' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 5,
                'check_permission_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 5,
                'check_permission_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 4,
                'check_permission_id' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 4,
                'check_permission_id' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 4,
                'check_permission_id' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 4,
                'check_permission_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 3,
                'check_permission_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 3,
                'check_permission_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => null,
                'sub_permission_id' => 3,
                'check_permission_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 1,
                'permission_id' => 1,
                'sub_permission_id' => null,
                'check_permission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}