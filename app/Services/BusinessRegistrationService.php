<?php

namespace App\Services;

use App\Models\User;
use App\Models\Business;
use App\Models\BusinessPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class BusinessRegistrationService
{
    /**
     * 🔹 Common User Creation Logic
     */
    private function createUser(array $data, bool $isAdminCreated = false)
    {
        $imagePath = $this->uploadFile($data['image'] ?? null, 'users/images');
        $signaturePath = $this->uploadFile($data['signature'] ?? null, 'users/signatures');

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => generateUniqueUsername($data['name']),
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'image' => $imagePath,
            'signature' => $signaturePath,
            'status' => $isAdminCreated ? 'approved' : 'pending',
        ]);
    }

    /**
     * 🔹 Common Business Creation Logic
     */
    private function createBusiness(array $data, $userId, bool $isAdminCreated = false)
    {
        $logoPath = $this->uploadFile($data['logo'] ?? null, 'businesses/logos');

        $business = Business::create([
            'name' => $data['company_name'],
            'slug' => SlugService::generateUniqueSlug($data['company_name'], Business::class),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'owner_id' => $userId,
            'logo' => $logoPath,
            'status' => $isAdminCreated ? 'active' : 'pending',
        ]);

        return $business;
    }

    /**
     * 🔹 Register Business Owner (Self-Registration)
     */
    public function registerOwner(array $data)
    {
        DB::beginTransaction();

        try {
            $user = $this->createUser($data, false);
            $business = $this->createBusiness($data, $user->id, false);
            $user->update(['business_id' => $business->id]);

            // $token = JWTAuth::fromUser($user);

            DB::commit();

            // Send Welcome Email
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (Exception $e) {
                // Log the error but don't fail the registration
                logger()->error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
            }

            return compact('user', 'business');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 🔹 Create Business Owner (By Admin)
     */
    public function createOwnerByAdmin(array $data)
    {
        DB::beginTransaction();

        try {
            $user = $this->createUser($data, true);
            $business = $this->createBusiness($data, $user->id, true);
            $user->update(['business_id' => $business->id]);

            // Assign "Business Admin" Role Automatically
            $role = Role::where('name', 'Business Admin')->first();
            if ($role) {
                $user->assignRole($role->name);
            }

            // Create Business Permissions for Admin created flow
            BusinessPermission::create([
                'business_id'      => $business->id,
                'user_can_login'   => $data['user_can_login'] ?? true,
                'commission'       => $data['commission'] ?? true,
                'template_can_add' => $data['template_can_add'] ?? true,
                'qb_integration'   => $data['qb_integration'] ?? true,
                'user_limit'       => $data['user_limit'] ?? 0,
            ]);

            DB::commit();

            // Send Welcome Email
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (Exception $e) {
                // Log the error but don't fail the registration
                logger()->error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
            }

            return compact('user', 'business');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * � Update Business Permissions
     */
    public function updateBusinessPermissions(Business $business, array $data)
    {
        return BusinessPermission::updateOrCreate(
            ['business_id' => $business->id],
            [
                'user_can_login'   => $data['user_can_login'] ?? true,
                'commission'       => $data['commission'] ?? true,
                'template_can_add' => $data['template_can_add'] ?? true,
                'qb_integration'   => $data['qb_integration'] ?? true,
                'user_limit'       => $data['user_limit'] ?? 0,
            ]
        );
    }

    /**
     * �🔸 File Upload Helper
     */
    public function uploadFile($file, $path)
    {
        if (!$file) return null;
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $fileName, 'public');
        return $path . '/' . $fileName; 
    }
}
