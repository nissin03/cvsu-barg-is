<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $isCanceledBySystem;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment, bool $isCanceledBySystem = false)
    {
        $this->payment = $payment;
        $this->isCanceledBySystem = $isCanceledBySystem;
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
        $facilityName = $this->payment->availability->facility->name ?? 'Unknown Facility';
        $canceledBy = $this->isCanceledBySystem ? 'System' : 'Staff';

        $dateRange = $this->getDateRange();

        $mailMessage = (new MailMessage)
            ->subject('Reservation Canceled - ' . $facilityName)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your reservation has been canceled.')
            ->line('**Reservation Details:**')
            ->line('• **Facility:** ' . $facilityName)
            ->line('• **Reservation ID:** #' . $this->payment->id)
            ->line('• **Date(s):** ' . $dateRange)
            ->line('• **Total Amount:** ₱' . number_format($this->payment->total_price, 2))
            ->line('• **Canceled By:** ' . $canceledBy);

        if ($this->payment->cancellation_reason) {
            $mailMessage->line('**Reason:** ' . $this->payment->cancellation_reason);
        }

        $mailMessage->action('View Reservation Details', route('user.reservation_details', $this->payment->id))
            ->line('If you have any questions or concerns, please contact us.')
            ->line('We apologize for any inconvenience this may have caused.');

        return $mailMessage;
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $facilityName = $this->payment->availability->facility->name ?? 'Unknown Facility';
        $dateRange = $this->getDateRange();
        $canceledBy = $this->isCanceledBySystem ? 'automatically by the system' : 'by staff';

        return [
            'payment_id' => $this->payment->id,
            'title' => 'Reservation Canceled',
            'body' => "Your reservation for {$facilityName} has been canceled {$canceledBy}.",
            'url' => route('user.reservation_details', $this->payment->id),
            'icon' => 'fas fa-calendar-times',
            'meta' => [
                'facility_name' => $facilityName,
                'date_range' => $dateRange,
                'total_price' => $this->payment->total_price,
                'status' => $this->payment->status,
                'cancellation_reason' => $this->payment->cancellation_reason,
                'canceled_at' => optional($this->payment->canceled_at)->toISOString(),
                'canceled_by' => $this->isCanceledBySystem ? 'system' : 'staff',
                'has_addons' => $this->payment->addonTransactions()->count() > 0,
            ]
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'type' => 'reservation_canceled',
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }
    protected function getDateRange(): string
    {
        $transactionReservations = $this->payment->transactionReservations;
        if ($transactionReservations->isEmpty()) {
            return '';
        }

        $dates = $transactionReservations
            ->pluck('availability.date_from')
            ->filter()
            ->map(fn($date) => Carbon::parse($date));

        if ($dates->isEmpty()) {
            return 'N/A';
        }

        $earliestDate = $dates->min();

        if ($dates->count() > 1) {
            $latestDate = $transactionReservations
                ->pluck('availability.date_to')
                ->filter()
                ->map(fn($date) => \Carbon\Carbon::parse($date))
                ->max();

            return $earliestDate->format('M d, Y') . ' - ' . $latestDate->format('M d, Y');
        }

        return $earliestDate->format('M d, Y');
    }
}
