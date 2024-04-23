<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        // 1. Validasi request
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 2. login
        $credentials = request(['username', 'password']);

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'User credentials not match',
            ], 422);
        }

        // 3. generate token
        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
        ], 200);
    }

    /**
     * Register user
     */
    public function register(StoreUserRequest $request)
    {
        // 1. Create User (jika belom ada akun admin, maka yg pertama kali register dibuatkan role admin)
        $findAdmin = User::where('role', 'admin')->first();
        $role = !$findAdmin ? 'admin' : 'user';
        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'role' => $role
        ]);

        // 2. return response
        return response()->json([
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // 1. Hapus token
        $request->user()->currentAccessToken()->delete();

        // 2. return
        return response()->json([
            'message' => 'Logout successfully',
        ], 200);
    }

    /**
     * Get current user
     */
    public function me()
    {
        return response()->json([
            'user' => auth()->user(),
        ], 200);
    }
}
