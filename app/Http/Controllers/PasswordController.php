<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function showSetPasswordForm()
    {
        $user = Auth::user();
        if ($user->password_set) {
            return redirect()->route('user.index')->with('info', 'Password is already set.');
        }
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verification.notice');
        }
        return view('auth.passwords.set');
    }

    public function setPassword(Request $request)
    {
        $user = Auth::user();

        // Double-check email is verified
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email first.');
        }
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->password_set = true;
        $user->save();

        $request->session()->regenerate();
        return redirect()->route('user.profile')->with([
            'incomplete_profile' => true,
            'message' => 'Your password has been successfully updated. Please complete your profile to proceed.'
        ]);
    }
}
