<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPrivacyController extends Controller
{
    public function showDataPrivacyNotice()
    {
        return view('auth.data-privacy-notice');
    }

    public function accept()
    {
        //
    }
}
