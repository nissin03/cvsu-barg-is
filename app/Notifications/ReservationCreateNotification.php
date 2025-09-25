<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReservationCreateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment,)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $facilityName = $this->payment->availability?->facility?->name ?? 'N/A';

        $dateFrom = $this->payment->availability?->date_from;
        $dateTo   = $this->payment->availability?->date_to;
        $dateRange = $dateFrom && $dateTo
            ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')
            : 'N/A';

        return (new MailMessage)
            ->subject('Facility Reservation Created - Pending Approval')
            ->markdown('emails.reservation-created', [
                'user'        => $notifiable,
                'payment'     => $this->payment,
                'facilityName' => $facilityName,
                'dateRange'   => $dateRange,
                'totalPrice'  => $this->payment->total_price,
                'actionUrl'   => route('user.reservation_details', $this->payment->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $facilityName = $this->payment->availability?->facility?->name ?? 'N/A';

        $dateFrom = $this->payment->availability?->date_from;
        $dateTo   = $this->payment->availability?->date_to;
        $dateRange = $dateFrom && $dateTo
            ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')
            : 'N/A';

        return [
            'payment_id'    => $this->payment->id,
            'title'         => 'Reservation Created',
            'body'          => "Your reservation for {$facilityName} has been created and is pending approval.",
            'url'           => route('user.reservation_details', $this->payment->id),
            'icon'          => 'fas fa-calendar-check',
            'facility_name' => $facilityName,
            'date_range'    => $dateRange,
            'total'         => $this->payment->total_price,
            'status'        => $this->payment->status,
            'message'       => "Reservation #{$this->payment->id} is being processed. You'll be notified once approved.",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }
}
