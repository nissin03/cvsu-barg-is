<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewContactMessage extends Notification
{
    use Queueable;

    protected $contact;
    /**
     * Create a new notification instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
            'title' => 'New Contact Message',
            'body' => "New message from {$this->contact->name}: " . \Str::limit($this->contact->message, 50),
            'url' => route('admin.contacts'),
            'icon' => 'fas fa-envelope',
            'name' => $this->contact->name, // Keep for backward compatibility
            'email' => $this->contact->email,
            'message' => $this->contact->message, // Keep for backward compatibility
            'time' => $this->contact->created_at->diffForHumans()
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'contact_id' => $this->contact->id,
            'name' => $this->contact->name,
            'email' => $this->contact->email,
            'message' => $this->contact->message,
            'time' => $this->contact->created_at->diffForHumans(),
        ]);
    }
}
