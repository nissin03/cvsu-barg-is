@component('mail::message')
    # Reservation Confirmation

    Hello {{ $user->name }},

    We've received your facility reservation request and it's currently under review.

    @component('mail::table')
        | | |
        |:--|--:|
        | **Reservation ID** | #{{ $payment->id }} |
        | **Facility** | {{ $facilityName }} |
        | **Date(s)** | {{ $dateRange }} |
        | **Amount** | ₱{{ number_format($totalPrice, 2) }} |
        | **Status** | <span
            style="background-color: #f59e0b; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">PENDING</span>
        |
    @endcomponent

    @component('mail::button', ['url' => $actionUrl, 'color' => 'primary'])
        View Details
    @endcomponent

    **Next Steps:**
    - Wait for admin approval
    - Check your email for updates
    - Prepare required documents
    - Visit facility on your scheduled date

    Questions? Contact our support team.

    Best regards,<br>
    {{ config('app.name') }}
@endcomponent
