<?php

namespace App\Listeners\Invoice;

use App\Notifications\Admin\InvoiceSentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class InvoiceEmailedNotification implements ShouldQueue
{

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
        $invitation = $event->invitation;

        foreach ($invitation->account->account_users as $account_user) {

            $account_user->user->notify(new InvoiceSentNotification($invitation, $invitation->account));

        }

        if (isset($invitation->account->slack_webhook_url)) {

            Notification::route('slack', $invitation->company->slack_webhook_url)
                        ->notify(new InvoiceSentNotification($invitation, $invitation->account, true));

        }
    }
}
