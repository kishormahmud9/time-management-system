<?php

namespace App\Services;

use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Spatie\Permission\Models\Role;

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

        return Business::create([
            'name' => $data['company_name'],
            'slug' => SlugService::generateUniqueSlug($data['company_name'], Business::class),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'owner_id' => $userId,
            'logo' => $logoPath,
            'status' => $isAdminCreated ? 'active' : 'pending',
        ]);
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

            // Assign Role
            $role = isset($data['role_id'])
                ? Role::find($data['role_id'])
                : Role::where('name', 'Business Admin')->first();

            if ($role) {
                $user->assignRole($role->name);
            }

            DB::commit();
            return compact('user', 'business');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 🔸 File Upload Helper
     */
    public function uploadFile($file, $path)
    {
        if (!$file) return null;
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $fileName, 'public');
        return 'storage/' . $path . '/' . $fileName;
    }
}
