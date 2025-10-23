<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Traits\UserActivityTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ProfileController extends Controller
{
    use UserActivityTrait;
    // ✅ user view profile
    public function view()
    {
        try {
            // ✅ Get authenticated user
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // ✅ Return user profile
            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (ValidationException $e) {
            // 🔸 Validation errors (rare for view)
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (TransportExceptionInterface $e) {
            // 🔸 Mail-related transport issue (future-proof)
            return response()->json([
                'success' => false,
                'message' => 'Mail transport failed.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // 🔸 Catch-all for unexpected issues
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ OTP Verification 
    public function edit(Request $request)
    {
        // dd($request->all());
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // ✅ Validation (exclude forbidden fields)
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married',
                'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
                'signature' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // ✅ Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('users/images', 'public');
                $validated['image'] = $imagePath;
            }

            if ($request->hasFile('signature')) {
                $signaturePath = $request->file('signature')->store('users/signatures', 'public');
                $validated['signature'] = $signaturePath;
            }
            // dd($validated);

            // ✅ Update allowed fields
            $user->update($validated);
            // dd($user);

            $this->logActivity('update_profile');
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
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

    // ✅ Change Password
    public function changePassword(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // ✅ Validation
            $request->validate([
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed', // needs new_password_confirmation
            ]);

            // ✅ Check old password
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Old password does not match',
                ], 400);
            }

            // ✅ Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            $this->logActivity('change_password');
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
