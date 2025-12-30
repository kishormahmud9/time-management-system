<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index(){
        // dd("here");
        $all_business = Business::get();
        // return view('business.index', compact('all_business'));
        return response()->json($all_business);
    }
}
