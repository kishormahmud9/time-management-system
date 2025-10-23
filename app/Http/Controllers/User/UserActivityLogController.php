<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserLog;
use Exception;
use Illuminate\Http\Request;

class UserActivityLogController extends Controller
{
    // Get all permissions
    public function view()
    {
        try {
            $activities = UserLog::latest()->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
