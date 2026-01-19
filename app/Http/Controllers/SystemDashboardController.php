<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemDashboardController extends Controller
{
    // view dashboard 
    public function view()
    {
        try {
            $business = Business::count();
            $activeBusiness = Business::where('status', 'active')->count();
            $user = User::count();
            return response()->json([
                'business' => $business,
                'activeBusiness' => $activeBusiness,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
