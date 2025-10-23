<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SlugService;
use App\Traits\UserActivityTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    use UserActivityTrait;
    // ✅ Register
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create user with generated username
            $user = User::create([
                'name' => $request->name,
                'username' => generateUniqueUsername($request->name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User successfully registered',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Register Busiiness Owner
    // public function registerBusinessOwner(Request $request)
    // {
    //     try {
    //         // ✅ Validation
    //         $validator = Validator::make($request->all(), [
    //             'name' => 'required|string|max:100',
    //             'email' => 'required|string|email|max:100|unique:users',
    //             'password' => 'required|string|min:6',
    //             'phone' => 'nullable|string|max:20',
    //             'company_name' => 'required|string|max:100',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'errors' => $validator->errors(),
    //             ], 422);
    //         }

    //         // ✅ Create user
    //         $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'username' => generateUniqueUsername($request->name),
    //             'password' => Hash::make($request->password),
    //             'phone' => $request->phone,
    //             'status' => 'pending', // Default waiting for admin approval
    //         ]);

    //         // ✅ Generate JWT token
    //         $token = JWTAuth::fromUser($user);

    //         // ✅ Create business
    //         $business = Business::create([
    //             'name' => $request->company_name,
    //             'slug' => SlugService::generateUniqueSlug($request->company_name, Business::class),
    //             'email' => $request->email,
    //             'phone' => $request->phone,
    //             'owner_id' => $user->id, // optional (if your Business model has owner_id)
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Registration submitted successfully. Waiting for admin approval.',
    //             'token' => $token,
    //             'user' => $user,
    //             'business' => $business,
    //         ], 201);
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Database related error
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Database error occurred.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     } catch (\Exception $e) {
    //         // Generic error
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong during registration.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function registerBusinessOwner(Request $request)
    {
        try {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:20',
                'company_name' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // ✅ DB Transaction (Best Practice)
            DB::beginTransaction();

            // ✅ Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => generateUniqueUsername($request->name),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => 'pending', // Waiting for admin approval
            ]);

            // ✅ Create business
            $business = Business::create([
                'name' => $request->company_name,
                'slug' => SlugService::generateUniqueSlug($request->company_name, Business::class),
                'email' => $request->email,
                'phone' => $request->phone,
                'owner_id' => $user->id, // optional
            ]);

            // ✅ Update user's business_id after business creation
            $user->update([
                'business_id' => $business->id,
            ]);

            // ✅ Generate JWT token
            $token = JWTAuth::fromUser($user);

            // ✅ Commit transaction
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registration submitted successfully. Waiting for admin approval.',
                'token' => $token,
                'user' => $user,
                'business' => $business,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if anything fails
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // ✅ Login
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. User not found.',
                ], 401);
            }

            if ($user->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not approved yet.',
                ], 403);
            }

            if (!$token = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Wrong password.',
                ], 401);
            }

            $this->logActivity('login');
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => Auth::user(),
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // JWT related error (token creation issue)
            return response()->json([
                'success' => false,
                'message' => 'Could not create token: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Any other unexpected error
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // ✅ logged out user and destroy auth token
    public function logout()
    {
        Auth::logout();
        $this->logActivity('logout');
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    // ✅ Refresh Token
    public function refresh()
    {
        try {
            $newToken = Auth::refresh();
            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
                'user' => Auth::user()
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    // ✅ Forget Password
    public function forgetPassword(Request $request)
    {
        try {
            // 🔹 Validate email
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            // 🔹 Get user
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // 🔹 Generate OTP (6-digit)
            $otp = rand(100000, 999999);

            // 🔹 Store or update OTP record
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(5),
                    'created_at' => now(),
                ]
            );

            // 🔹 Send OTP email
            Mail::raw("Your password reset OTP is: $otp (valid for 5 minutes)", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP');
            });

            // 🔹 Success response
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email address',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔸 Validation error
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (TransportExceptionInterface $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check mail configuration.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // ✅ OTP Verification 
    public function otpVerify(Request $request)
    {
        try {
            // ✅ Validate user input
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:6',
            ]);

            // ✅ Fetch password reset record
            $record = DB::table('password_resets')->where('email', $request->email)->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email',
                ], 404);
            }

            // ✅ Check OTP match
            if ($record->otp != $request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP',
                ], 400);
            }

            // ✅ Check expiry
            if (now()->greaterThan($record->expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP expired',
                ], 400);
            }

            // ✅ Mark as verified
            DB::table('password_resets')
                ->where('email', $request->email)
                ->update(['is_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (TransportExceptionInterface $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mail transport failed.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Reset Password
    public function resetPassword(Request $request)
    {

        dd($request->all());
        try {
            // ✅ Validate request inputs
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:8|confirmed'
            ]);

            // ✅ Check if OTP verified
            $record = DB::table('password_resets')->where('email', $request->email)->first();

            if (!$record || empty($record->is_verified)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP not verified',
                ], 403);
            }

            // ✅ Update user password
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // ✅ Delete password reset record
            DB::table('password_resets')->where('email', $request->email)->delete();

            $this->logActivity('reset_password');
            return response()->json([
                'success' => true,
                'message' => 'Password reset successful',
            ], 200);
        } catch (ValidationException $e) {
            // Validation errors
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (TransportExceptionInterface $e) {
            // Mail-related transport issue (future-proof)
            return response()->json([
                'success' => false,
                'message' => 'Mail transport failed.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Catch-all for unexpected issues
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
