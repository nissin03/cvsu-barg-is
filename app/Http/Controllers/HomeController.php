<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Slide;
use App\Models\Rental;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Helpers\ProfileHelper;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\ContactMessageReceived;
use App\Notifications\NewContactMessage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContactMessageNotification;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::where('status', 1)->get()->take(3);
        $categories = Category::orderBy('name')->get();
        $fproducts = Product::where('featured', 1)->get()->take(8);
        return view('index', compact('slides', 'categories', 'fproducts'));
    }
    public function contact()
    {
        $user = Auth::user();
        return view('contact', compact('user'));
    }
    public function contact_store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->withErrors([
                'no_account' => 'You need to log in to send a message.'
            ]);
        }
        if ($user->utype === 'USR' && ProfileHelper::isProfileIncomplete($user)) {
            return redirect()->route('user.profile', ['swal' => 1])->with([
                'message' => 'Your profile information is incomplete. Please update your profile (name, email, and phone number) to send a message.'
            ]);
        }

        $request->validate([
            'message' => 'required|max:65535',
        ], [
            'message.required' => 'The message field is required.',
            'message.max' => 'The message must not exceed 65535 characters.'
        ]);

        $lastContact = Contact::where('user_id', $user->id)
            ->latest()
            ->first();
        $timeWindow = 60;

        if ($lastContact && Carbon::parse($lastContact->created_at)->diffInMinutes(Carbon::now()) < $timeWindow) {
            return redirect()->back()->with('error', 'You can only send one message every ' . $timeWindow . ' minutes.');
        }

        $contact = new Contact();
        $contact->name = $user->name;
        $contact->email = $user->email;
        $contact->phone = $user->phone_number;
        $contact->message = $request->message;
        $contact->user_id = $user->id;
        $contact->save();

        $admin = User::where('utype', 'ADM')->get();
        Notification::send($admin, new NewContactMessage($contact));
        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }

    public function search(Request  $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
