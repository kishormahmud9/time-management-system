<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // ✅ Register
    public function register(Request $request)
    {
        // dd("hello jihad");
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function registerBusinessOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed', // include password_confirmation
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'business_owner',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Registration submitted successfully. Waiting for admin approval.',
            'user' => $user,
        ], 201);
    }


    // ✅ Login
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {
    //             return response()->json(['error' => 'Invalid credentials'], 401);
    //         }
    //     } catch (JWTException $e) {
    //         return response()->json(['error' => 'Could not create token'], 500);
    //     }

    //     return response()->json([
    //         'token' => $token,
    //         'user' => auth()->user()
    //     ]);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Validation
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check user first
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Prevent login if user not active
        if ($user->status !== 'active') {
            return response()->json(['error' => 'Your account is not approved yet.'], 403);
        }



        // Attempt to verify credentials and create a token
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Return token + user info
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => auth()->user(),
        ]);
    }

    // ✅ Get logged in user info
    public function me()
    {
        return response()->json(auth()->user());
    }

    // logged out user and destroy auth token
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh();
            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
                'user' => auth()->user()
            ]);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
