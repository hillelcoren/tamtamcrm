<?php

namespace App\Notifications\Admin;

use App\Utils\Number;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class InvoiceViewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $invitation;
    protected $invoice;
    protected $account;
    protected $settings;
    public $is_system;
    protected $contact;

    public function __construct($invitation, $account, $is_system = false, $settings = null)
    {
        $this->invoice = $invitation->invoice;
        $this->contact = $invitation->contact;
        $this->account = $account;
        $this->settings = $invitation->contact->customer->getMergedSettings();
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

        $amount = Number::formatMoney($this->invoice->amount, $this->invoice->customer);
        $subject = trans('texts.notification_invoice_viewed_subject', [
            'client' => $this->contact->present()->name(),
            'invoice' => $this->invoice->number,
        ]);

        $data = [
            'title' => $subject,
            'test' => trans('texts.notification_invoice_viewed', [
                'amount' => $amount,
                'client' => $this->contact->present()->name(),
                'invoice' => $this->invoice->number,
            ]),
            'url' => config('ninja.site_url') . '/invoices/' . $this->invoice->id,
            'button' => trans('texts.view_invoice'),
            'signature' => !empty($this->settings) ? $this->settings->email_signature : '',
            'logo' => $this->account->present()->logo(),
        ];


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
        $amount = Number::formatMoney($this->invoice->amount, $this->invoice->customer);

        return (new SlackMessage)->success()->from(trans('texts.notification_bot'))->image($logo)
                                 ->content(trans('texts.notification_invoice_viewed', [
                                     'amount' => $amount,
                                     'client' => $this->contact->present()->name(),
                                     'invoice' => $this->invoice->number,
                                 ]));
    }

}
