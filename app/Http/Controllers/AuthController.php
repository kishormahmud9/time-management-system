<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\BusinessRegistrationService;
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
use App\Mail\WelcomeEmail;
use App\Mail\OTPEmail;
use App\Mail\PasswordResetSuccessEmail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use UserActivityTrait;

    protected $businessService;

    public function __construct(BusinessRegistrationService $businessService)
    {
        $this->businessService = $businessService;
    }
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

            // Send welcome email
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

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

            $data = $this->businessService->registerOwner($request->all());

            // Send welcome email to business owner
            try {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    Mail::to($user->email)->send(new WelcomeEmail($user));
                }
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                Log::error('Failed to send welcome email to business owner: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration submitted successfully.',
                'data' => $data
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
            // 1) Validate input
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

            // 2) Try login first (email + password একসাথে)
            if (!$token = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])) {

                // Check if email exists
                $userExists = \App\Models\User::where('email', $request->email)->exists();

                return response()->json([
                    'success' => false,
                    'message' => $userExists
                        ? 'Wrong password.'
                        : 'Email does not exist.',
                ], 401);
            }

        // 3) Login successful → now get user
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // 4) Status check
            if ($user->status !== 'approved') {

                // token invalidate kore dei
                Auth::logout();

                if ($user->status === 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is pending approval. Please wait for an admin to review your request.',
                    ], 403);
                }

                if ($user->status === 'rejected') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account request has been rejected. Please contact support if you think this is a mistake.',
                    ], 403);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not allowed to log in.',
                ], 403);
            }

            // 5) ✅ Role check (Spatie roles)
            $roles = $user->getRoleNames();

            if ($roles->isEmpty()) {
                // kono role assign nai → login allow korbo na
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'No role is assigned to your account. Please contact the administrator.',
                ], 403);
            }

            $role = $roles->first();

            // 6) Everything ok → login successful
            $this->logActivity('login');

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => $user,
                'role'    => $role,
            ], 200);
        } catch (\Exception $e) {
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
                'message' => 'Token Refreshed Successfully',
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
            Mail::to($user->email)->send(new OTPEmail($user, $otp));

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
        // dd($request->all());
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

            // Send password reset success email
            try {
                Mail::to($user->email)->send(new PasswordResetSuccessEmail($user));
            } catch (\Exception $e) {
                // Log email error but don't fail password reset
                \Log::error('Failed to send password reset success email: ' . $e->getMessage());
            }

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
