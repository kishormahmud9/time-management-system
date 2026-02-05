<?php

namespace App\Http\Middleware;

use App\Services\BusinessPermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessLogin
{
    protected $permissionService;

    public function __construct(BusinessPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->business_id) {
            // Check if user is System Admin (usually has role_id 1 or a specific role name)
            // System Admin should bypass this check
            // Assuming role_id 1 is System Admin or checking role name
            $isSystemAdmin = $user->hasRole('System Admin'); // Using Spatie role check if available

            if (!$isSystemAdmin) {
                if (!$this->permissionService->canLogin($user->business_id)) {
                    // If login is disabled, logout and return error
                    Auth::guard('web')->logout(); // Logout for session-based
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Login is currently disabled for your organization. Please contact your administrator.',
                        'error_code' => 'BUSINESS_LOGIN_DISABLED'
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
