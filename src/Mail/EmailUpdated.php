<?php

namespace Javaabu\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\Messages\MailMessage;

class EmailUpdated extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $user;

    /**
     * The new email
     *
     * @var string
     */
    public $new_email;

    /**
     * Create a new message instance.
     * @param $user
     * @param $new_email
     */
    public function __construct($user, $new_email)
    {
        $this->user = $user;
        $this->new_email = $new_email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = (new MailMessage())
            ->greeting("Hi {$this->user->name},")
            ->line("We have updated your email address to {$this->new_email}.")
            ->line('You can no longer login using your old email address.')
            ->line('If you did not make this request, contact us immediately.');

        return $this->markdown('vendor.notifications.email')->with($message->data());
    }
}
