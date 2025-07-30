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
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\ContactMessageReceived;
use App\Notifications\ContactMessageNotification;

class HomeController extends Controller
{
        public function index(Request $request)
    {
        $slides = Slide::where('status', 1)->take(3)->get();
         
        $categories = Category::whereNotNull('parent_id')->take(3)->get();
        
        $fproducts = Product::where('featured', 1)
        ->where('stock_status', '!=', 'outofstock')
        ->where('quantity', '>', 0)
        ->get()
        ->take(8);

        return view('index', compact('slides', 'categories','fproducts'));  

    }
  
    public function contact()
    {
        return view('contact');
    }
    public function contact_store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->withErrors([
                'no_account' => 'You need to log in to send a message.'
            ]);
        }

        if (!$user->name || !$user->email || !$user->phone_number) {
            return redirect()->back()->withErrors([
                'user_info' => 'Your profile information is incomplete. Please update your profile (name, email, and phone number) to send a message.'
            ]);
        }

        $request->validate([
            'message' => 'required|max:65535',
        ], [
            'message.required' => 'The message field is required.',
            'message.max' => 'The message must not exceed 65535 characters.'
        ]);

        $todaysMessagesCount = Contact::where('user_id', $user->id)
                                    ->whereDate('created_at', today())
                                    ->count();

        if ($todaysMessagesCount >= 3) {
            return redirect()->back()->withErrors([
                'message_limit' => 'You have reached your daily limit of 3 messages. Please try again tomorrow.'
            ]);
        }
        // Save the contact message
        $contact = new Contact();
        $contact->name = $user->name;
        $contact->email = $user->email;
        $contact->phone = $user->phone_number;
        $contact->message = $request->message;
        $contact->user_id = $user->id;
        $contact->save();

        // Notify admin (uncomment if you have this functionality)
        // $admin = User::where('utype', 'ADM')->first();
        // if ($admin) {
        //     $admin->notify(new ContactMessageNotification($contact));
        // }

        // Broadcast event (uncomment if you're using broadcasting)
        // broadcast(new ContactMessageReceived($contact));

        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }

    public function search(Request  $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
