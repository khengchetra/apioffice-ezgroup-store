<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gender;
use App\Models\Branch;
use App\Models\Position;
use App\Models\Role;
use App\Models\Department;
use App\Models\Category;
use App\Models\Size;
use App\Models\Color;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $genders = Gender::where('is_show', true)->get(['id', 'gender_name', 'remark']);
        $branches = Branch::where('is_show', true)->get(['id', 'branch_name', 'remark']);
        $positions = Position::where('is_show', true)->get(['id', 'position_name', 'remark']);
        $roles = Role::where('is_show', true)->get(['id', 'role_name', 'remark']);
        $departments = Department::where('is_show', true)->get(['id', 'department_name', 'remark']);
        $categories = Category::where('is_show', true)->get(['id', 'name_category', 'remark']);

        return response()->json([
            'genders' => $genders,
            'branches' => $branches,
            'positions' => $positions,
            'roles' => $roles,
            'departments' => $departments,
            'categories' => $categories,
        ]);
    }
}