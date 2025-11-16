<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityLogController extends Controller
{
    // Get all permissions
    // Get activity logs with role-based filtering
    public function view(Request $request)
    {
        try {
            $actor = Auth::user(); // current authenticated user

            if (! $actor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Base query
            $query = UserLog::query();

            // Role-based filtering:
            // 1) System Admin -> no filter (see all)
            // 2) Business Admin -> filter by actor's business_id
            // 3) Other users -> only their own logs (by user_id)
            if ($actor->hasRole('System Admin')) {
                // no extra where: admin sees all activities
            } elseif ($actor->hasRole('Business Admin')) {
                // ensure actor has a business_id set
                if (! isset($actor->business_id) || ! $actor->business_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Business information not found for this user.'
                    ], 403);
                }

                $query->where('business_id', $actor->business_id);
            } else {
                // regular user: only his/her own activities
                $query->where('user_id', $actor->id);
            }

            // Optional: allow client to paginate or limit results
            // e.g., ?page=2 (uses simplePagination) or ?per_page=50
            $perPage = (int) $request->query('per_page', 50);
            if ($perPage <= 0) {
                $perPage = 50;
            }

            // Return latest entries, paginated
            $activities = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $activities
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
