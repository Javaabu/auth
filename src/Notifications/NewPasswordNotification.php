<?php

namespace Javaabu\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Javaabu\Auth\User;

class NewPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new notification instance.
     *
     * @param string $password
     */
    public function __construct(
        protected string $password)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $notifiable
     * @return array
     */
    public function via(User $notifiable): array
    {
        return [
            'mail'
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  User  $notifiable
     * @return MailMessage
     */
    public function toMail(User $notifiable): MailMessage
    {
        $user_type = slug_to_title($notifiable->getMorphClass());
        $account_name = get_setting('app_name').' '.$user_type.' Account';

        $message = (new MailMessage())
                ->subject($account_name.' Login Details')
                ->greeting("Hi {$notifiable->name},")
                ->line("The login details of your $account_name are given below:")
                ->line("**Email:** {$notifiable->email}")
                ->line("**Password:** {$this->password}");


        if ($notifiable->requiresPasswordUpdate()) {
            $message->line('You will be required to change your password after your next login.');
        } else {
            $message->line('We recommend that you change your password after your next login.');
        }

        $message->action('Login Now', $notifiable->loginUrl());

        return $message;
    }
}
