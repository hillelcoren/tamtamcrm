<?php

namespace App\Listeners\Payment;

use App\Notifications\Admin\NewPaymentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;

class PaymentNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $payment = $event->payment;

        //$invoices = $payment->invoices;

        if (!empty($payment->account->account_users)) {
            foreach ($payment->account->account_users as $account_user) {
                $account_user->user->notify(new NewPaymentNotification($payment, $payment->account));
            }
        }

        if (isset($payment->account->slack_webhook_url)) {
            Notification::route('slack', $payment->account->slack_webhook_url)
                        ->notify(new NewPaymentNotification($payment, $payment->account, true));
        }
    }
}
