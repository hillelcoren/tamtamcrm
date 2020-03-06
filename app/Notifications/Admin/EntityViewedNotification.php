<?php

namespace App\Notifications\Admin;

use App\Utils\Number;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class EntityViewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     * @
     */

    protected $invitation;
    protected $entity_name;
    protected $entity;
    protected $account;
    protected $settings;
    public $is_system;
    protected $contact;

    public function __construct($invitation, $entity_name, $is_system = false, $settings = null)
    {
        $this->entity_name = $entity_name;
        $this->entity = $invitation->{$entity_name};
        $this->contact = $invitation->contact;
        $this->account = $invitation->account;
        $this->settings = $this->entity->customer->getMergedSettings();
        $this->is_system = $is_system;
        $this->invitation = $invitation;

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
        $data = $this->buildDataArray();
        $subject = $this->buildSubject();

        return (new MailMessage)->subject($subject)->markdown('email.admin.generic', $data);
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
        $amount = Number::formatMoney($this->entity->amount, $this->entity->client);

        return (new SlackMessage)->from(trans('texts.notification_bot'))->success()
                                 ->image('https://app.invoiceninja.com/favicon-v2.png')
                                 ->content(trans("texts.notification_{$this->entity_name}_viewed", [
                                     'amount' => $amount,
                                     'client' => $this->contact->present()->name(),
                                     $this->entity_name => $this->entity->number
                                 ]))->attachment(function ($attachment) use ($amount) {
                $attachment->title(trans('texts.entity_number_placeholder',
                    ['entity' => ucfirst($this->entity_name), 'entity_number' => $this->entity->number]),
                    $this->invitation->getAdminLink())->fields([
                    trans('texts.client') => $this->contact->present()->name(),
                    trans('texts.status_viewed') => $this->invitation->viewed_date,
                ]);
            });
    }

    private function buildDataArray()
    {

        $amount = Number::formatMoney($this->entity->amount, $this->entity->customer);

        $data = [
            'title' => $this->buildSubject(),
            'test' => trans("texts.notification_{$this->entity_name}_viewed", [
                'amount' => $amount,
                'client' => $this->contact->present()->name(),
                $this->entity_name => $this->entity->number,
            ]),
            'url' => config('ninja.site_url') . "/{$this->entity_name}s/" . $this->entity->id,
            'button' => trans("texts.view_{$this->entity_name}"),
            'signature' => !empty($this->settings) ? $this->settings->email_signature : '',
            'logo' => $this->account->present()->logo(),
        ];


        return $data;

    }

    private function buildSubject()
    {
        $subject = trans("texts.notification_{$this->entity_name}_viewed_subject", [
            'client' => $this->contact->present()->name(),
            $this->entity_name => $this->entity->number,
        ]);

        return $subject;

    }
}
