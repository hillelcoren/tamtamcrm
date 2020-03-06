<?php

namespace App\Notifications\Admin;

use App\Utils\Number;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewPartialPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $payment;
    protected $account;
    protected $settings;
    protected $is_system;

    public function __construct($payment, $account, $is_system = false, $settings = null)
    {
        $this->payment = $payment;
        $this->account = $account;
        $this->settings = $payment->customer->getMergedSettings();
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
        $amount = Number::formatMoney($this->payment->amount, $this->payment->customer);

        $invoice_texts = trans('texts.invoice_number_short');

        foreach ($this->payment->invoices as $invoice) {
            $invoice_texts .= $invoice->number . ',';
        }

        $invoice_texts = substr($invoice_texts, 0, -1);

        $data = [
            'title' => trans('texts.notification_partial_payment_paid_subject',
                ['client' => $this->payment->customer->present()->name()]),
            'test' => trans('texts.notification_partial_payment_paid', [
                'amount' => $amount,
                'client' => $this->payment->customer->present()->name(),
                'invoice' => $invoice_texts,
            ]),
            'url' => config('ninja.site_url') . '/payments/' . $this->payment->id,
            'button' => trans('texts.view_payment'),
            'signature' => !empty($this->settings) ? $this->settings->email_signature : '',
            'logo' => $this->account->present()->logo(),
        ];


        return (new MailMessage)->subject(trans('texts.notification_partial_payment_paid_subject',
            ['client' => $this->payment->customer->present()->name()]))->markdown('email.admin.generic', $data);


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
        $amount = Number::formatMoney($this->payment->amount, $this->payment->customer);
        $invoice_texts = trans('texts.invoice_number_short');

        foreach ($this->payment->invoices as $invoice) {
            $invoice_texts .= $invoice->number . ',';
        }

        $invoice_texts = substr($invoice_texts, 0, -1);

        return (new SlackMessage)->success()
            //->to("#devv2")
                                 ->from("System")->image($logo)->content(trans('texts.notification_payment_paid', [
                'amount' => $amount,
                'client' => $this->payment->customer->present()->name(),
                'invoice' => $invoice_texts
            ]));
    }

}
