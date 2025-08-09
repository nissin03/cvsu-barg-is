<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{


    public function show_profile()
    {

        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function update_phone(Request $request)
    {
        $user = Auth::user();
        $request->validate(
            [
                'phone_number' => ['required', 'regex:/^9\d{9}$/'],
            ],
            [
                'phone_number.required' => 'Phone number is required.',
                'phone_number.regex' => 'Phone number must start with 9 and be exactly 10 digits.',
            ]
        );
        $normalizedPhone = preg_replace('/^(?:\+?63|0)/', '', $request->phone_number);
        $normalizedPhone = '+63' . $normalizedPhone;
        $user->update(['phone_number' => $normalizedPhone]);

        return redirect()->route('admin.profile.index')->with('success', 'Phone Number updated successfully!');
    }

    public function update_profile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }
        $normalizedPhone = $request->phone_number;
        $normalizedPhone = preg_replace('/^(?:\+?63|0)/', '', $normalizedPhone);
        $normalizedPhone = '+63' . $normalizedPhone;

        $user->update([
            'phone_number' => $normalizedPhone,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.profile.index')->with('success', 'Password updated successfully!');
    }

    public function update_profile_image(Request $request)
    {
        $user = Auth::user();
        abort_if($user->utype !== 'ADM', 403, 'Unauthorized.');

        $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'profile_image.required' => 'Please select an image to upload.',
            'profile_image.image' => 'The file must be an image.',
            'profile_image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'profile_image.max' => 'The image may not be greater than 2MB.',
        ]);

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $imagePath = $request->file('profile_image')->store('profile_images', 'public');

        $user->update([
            'profile_image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully!',
            'image_url' => $user->profile_image_url
        ]);
    }
}
