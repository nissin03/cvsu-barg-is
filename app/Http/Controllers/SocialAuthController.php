<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }



    public function handleGoogleCallback()
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();

            $email = $socialiteUser->getEmail();

            if (!str_ends_with($email, '@cvsu.edu.ph') && !str_ends_with($email, '@gmail.com')) {
                return redirect()->route('login')
                    ->withErrors('Please use your @cvsu.edu.ph or @gmail.com email address.');
            }
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $socialiteUser->getName(),
                    'password' => Hash::make(uniqid()),
                ]
            );

            if ($user->wasRecentlyCreated && is_null($user->email_verified_at)) {
                $user->sendEmailVerificationNotification();
            }

            Auth::login($user);

            if (is_null($user->email_verified_at)) {
                return redirect()->route('verification.notice');
            }

            if (!$user->password_set) {
                return redirect()->route('password.set');
            }

            return redirect()->intended('/');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors('Login failed, please try again.');
        }
    }
}
