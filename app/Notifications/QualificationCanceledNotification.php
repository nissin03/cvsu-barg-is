<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QualificationCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $qualification;

    /**
     * Create a new notification instance.
     */
    public function __construct($qualification)
    {
        $this->qualification = $qualification;
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
    public function toMail($notifiable): MailMessage
    {
        $facilityName = $this->qualification->availability->facility->name ?? 'Unknown Facility';

        // Get the payment/reservation ID
        $paymentId = $this->qualification->availability->payments()->first()->id ?? null;

        $mailMessage = (new MailMessage)
            ->subject('Qualification Request Canceled - ' . $facilityName)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your qualification request has been canceled.')
            ->line('**Details:**')
            ->line('• **Facility:** ' . $facilityName)
            ->line('• **Qualification ID:** #' . $this->qualification->id)
            ->line('• **Status:** Canceled')
            ->line('• **Reason:** ' . ($this->qualification->canceled_reason ?? 'No reason provided.'));

        // Only add action button if we have a payment ID
        if ($paymentId) {
            $mailMessage->action('View Details', route('user.reservation_details', ['payment_id' => $paymentId]));
        }

        $mailMessage->line('Please contact support if you believe this is a mistake.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $facilityName = $this->qualification->availability->facility->name ?? 'Unknown Facility';

        // Get the payment/reservation ID
        $paymentId = $this->qualification->availability->payments()->first()->id ?? null;

        // Generate URL only if payment ID exists, otherwise use a fallback
        $url = $paymentId
            ? route('user.reservation_details', ['payment_id' => $paymentId])
            : route('user.index'); // or any other fallback route

        return [
            'qualification_id' => $this->qualification->id,
            'title' => 'Qualification Canceled',
            'body' => "Your qualification request for {$facilityName} has been canceled.",
            'url' => $url,
            'icon' => 'fas fa-user-times',
            'meta' => [
                'facility_name' => $facilityName,
                'status' => 'canceled',
                'canceled_reason' => $this->qualification->canceled_reason,
                'canceled_at' => now()->toISOString(),
                'payment_id' => $paymentId,
            ]
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'type' => 'qualification_canceled',
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }
}
