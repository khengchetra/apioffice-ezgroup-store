<?php

namespace App\Http\Controllers\Api\UserManagment;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select([
            'id',
            'first_name',
            'last_name',
            'username',
            'employee_code',
            'number_phone',
            'email',
            'profile_image',
            'cover_image',
            'date_of_birth',
            'start_work',
            'bank_account_number',
            'other',
            'gender_id',
            'role_id',
            'branch_id',
            'department_id',
            'position_id',
            'is_show'
        ])
        ->where('is_show', 1)
        ->with(['gender', 'role', 'branch', 'department', 'position'])
        ->orderBy('id', 'desc')
        ->get();

        $users->each(function ($user) {
            if ($user->profile_image) {
                $user->profile_url = asset('storage/user/profile/' . $user->profile_image);
            }
            if ($user->cover_image) {
                $user->cover_url = asset('storage/user/cover/' . $user->cover_image);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::with(['gender', 'role', 'branch', 'department', 'position', 'creator'])
            ->findOrFail($id);

        if ($user->profile_image) {
            $user->profile_url = asset('storage/user/profile/' . $user->profile_image);
        }
        if ($user->cover_image) {
            $user->cover_url = asset('storage/user/cover/' . $user->cover_image);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'employee_code' => 'nullable|string|max:50|unique:users',
            'number_phone' => 'nullable|string|max:20|unique:users',
            'date_of_birth' => 'nullable|date',
            'start_work' => 'nullable|date',
            'bank_account_number' => 'nullable|string|max:255|unique:users',
            'other' => 'nullable|string',
            'gender_id' => 'nullable|exists:gender,id', // Fixed table name to match model (assuming Genders model/table)
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|exists:users,id', // Added validation for user_id (creator)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); 
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['is_show'] = 1;
        $data['user_id'] = auth()->id(); // Fixed: Set creator as current authenticated user

        $user = User::create($data);
        $user->load(['gender', 'role', 'branch', 'department', 'position', 'creator']); // Added 'creator' to load
        
        $this->addImageUrls($user);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'email' => 'sometimes|nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8',
            'employee_code' => 'sometimes|nullable|string|max:50|unique:users,employee_code,' . $id,
            'number_phone' => 'sometimes|nullable|string|max:20|unique:users,number_phone,' . $id,
            'date_of_birth' => 'sometimes|nullable|date',
            'start_work' => 'sometimes|nullable|date',
            'bank_account_number' => 'sometimes|nullable|string|max:255|unique:users,bank_account_number,' . $id,
            'other' => 'sometimes|nullable|string',
            'gender_id' => 'sometimes|nullable|exists:gender,id',
            'role_id' => 'sometimes|required|exists:roles,id',
            'branch_id' => 'sometimes|nullable|exists:branches,id',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'position_id' => 'sometimes|nullable|exists:positions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->load(['gender', 'role', 'branch', 'department', 'position']);
        
        $this->addImageUrls($user);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->is_show = 0;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User hidden successfully'
        ]);
    }

    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = User::where('username', $request->username);
        if ($request->filled('user_id')) {
            $query->where('id', '!=', $request->user_id);
        }

        $exists = $query->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'message' => $exists ? 'Username already taken' : 'Username available',
        ]);
    }

    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = User::where('email', $request->email);
        if ($request->filled('user_id')) {
            $query->where('id', '!=', $request->user_id);
        }

        $exists = $query->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'message' => $exists ? 'Email already taken' : 'Email available',
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $isValid = Hash::check($request->password, $user->password);

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'message' => $isValid ? 'Password is correct' : 'Incorrect password',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect old password',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    public function updateProfileImage(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete('user/profile/' . $user->profile_image);
            }
            $user->update(['profile_image' => $this->saveImage($request->file('profile_image'), 'user/profile')]);
        }

        $user->load(['gender', 'role', 'branch', 'department', 'position']);
        $this->addImageUrls($user);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Profile image updated successfully'
        ]);
    }

    // Helper methods
    private function saveImage($file, $path)
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $randomString = Str::random(100);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$timestamp}_{$randomString}.{$extension}";

        $file->storeAs($path, $filename, 'public');

        return $filename;
    }

    private function addImageUrls($user)
    {
        if ($user->profile_image) {
            $user->profile_url = asset('storage/user/profile/' . $user->profile_image);
        }
        if ($user->cover_image) {
            $user->cover_url = asset('storage/user/cover/' . $user->cover_image);
        }
    }
}