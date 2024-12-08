<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Price;

class UserFacilityController extends Controller
{
  
    public function index()
    {

        $facilities = Facility::with('prices')->latest()->paginate(6);
        // dd($facilities);
        return view('user.facilities.index', compact('facilities'));
    }
    
    public function show($slug)
    {
        
        $facility = Facility::where('slug', $slug)->with('prices')->firstOrFail();
        
    
        return view('user.facilities.details', compact('facility'));
    }
    
    
}
