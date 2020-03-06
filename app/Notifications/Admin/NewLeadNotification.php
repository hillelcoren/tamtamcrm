<?php

namespace App\Notifications\Admin;

use App\Utils\Number;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $lead;
    protected $account;
    protected $settings;
    protected $is_system;

    public function __construct($lead, $account, $is_system = false)
    {
        $this->lead = $lead;
        $this->account = $account;
        $this->settings = $account->settings;
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
        return $this->is_system ? ['slack'] : ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = [
            'title' => trans('texts.notification_lead_subject', ['client' => $this->lead->present()->name()]),
            'test' => trans('texts.notification_lead', [
                'client' => $this->lead->present()->name()
            ]),
            'url' => config('ninja.site_url') . '/payments/' . $this->lead->id,
            'button' => trans('texts.view_deal'),
            'signature' => !empty($this->settings) ? $this->settings->email_signature : '',
            'logo' => $this->account->present()->logo(),
        ];

        return (new MailMessage)->subject(trans('texts.notification_lead_subject',
            ['client' => $this->lead->present()->name]))->markdown('email.admin.generic', $data);
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
        $logo = $this->account->present()->logo();

        return (new SlackMessage)->success()
            //->to("#devv2")
                                 ->from("System")->image($logo)->content(trans('texts.notification__deal',
                ['client' => $this->lead->present()->name()]));
    }

}
