<?php

namespace App\Notifications\Ninja;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $user;

    protected $account;

    public $is_system;

    public function __construct($user, $account, $is_system = false)
    {
        $this->user = $user;
        $this->account = $account;
        $this->is_system = $is_system;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $user_name = $this->user->first_name . " " . $this->user->last_name;
        $email = $this->user->email;
        $ip = $this->user->ip;

        $data = [
            'title' => trans('texts.new_signup'),
            'message' => trans('texts.new_signup_text', ['user' => $user_name, 'email' => $email, 'ip' => $ip]),
            'url' => config('ninja.web_url'),
            'button' => trans('texts.account_login'),
            'signature' => $this->account->settings->email_signature,
            'logo' => $this->account->present()->logo(),
        ];


        return (new MailMessage)->subject(ctrans('texts.new_signup'))->markdown('email.admin.generic', $data);


    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [//
        ];
    }

    public function toSlack($notifiable)
    {

        $this->user->setAccount($this->$account);

        $user_name = $this->user->first_name . " " . $this->user->last_name;
        $email = $this->user->email;
        $ip = $this->user->ip;

        return (new SlackMessage)->success()->from(trans('texts.notification_bot'))
                                 ->image('https://app.invoiceninja.com/favicon.png')
                                 ->content("A new account has been created by {$user_name} - {$email} - from IP: {$ip}");
    }
}
