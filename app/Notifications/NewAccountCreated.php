<?php

namespace App\Notifications;

use App\Mail\Signup\NewSignup;
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

    public function __construct($user, $account)
    {
        $this->user = $user;
        $this->account = $account;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
        //return ['slack','mail'];
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
        //$ip = $this->user->ip;
        $ip = '';

        $data = [
            'title' => trans('texts.new_signup'),
            'message' => trans('texts.new_signup_text', ['user' => $user_name, 'email' => $email, 'ip' => $ip]),
            'url' => config('taskmanager.web_url'),
            'button' => trans('texts.account_login'),
            'signature' => '',
        ];


        return (new MailMessage)->subject(trans('texts.new_signup'))->markdown('email.admin.generic', $data);


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

        $user_name = $this->user->first_name . " " . $this->user->last_name;
        $email = $this->user->email;
        $ip = $this->user->ip;

        return (new SlackMessage)->success()->to("#devv2")->from("System")
                                 ->image('https://app.invoiceninja.com/favicon.png')
                                 ->content("A new account has been created by {$user_name} - {$email} - from IP: {$ip}");
    }
}
