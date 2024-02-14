<?php

namespace Javaabu\Auth\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailUpdateRequest extends Notification
{
    /**
     * The new email
     *
     * @var string
     */
    public string $new_email;

    /**
     * Create a notification instance.
     * @param $new_email
     */
    public function __construct($new_email)
    {
        $this->new_email = $new_email;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via(mixed $notifiable): array|string
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->greeting("Hi {$notifiable->name},")
            ->line("We have received a request to update your email address to {$this->new_email}.")
            ->line('Your email address won\'t be updated until you verify the new email address.')
            ->line('If you did not make this request, contact us immediately.');
    }
}
