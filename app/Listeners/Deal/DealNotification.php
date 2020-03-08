<?php

namespace App\Listeners\Deal;

use App\Notifications\Admin\NewPaymentNotification;
use App\Notifications\Admin\NewDealNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;

class DealNotification implements ShouldQueue
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
        $deal = $event->deal;

        if (!empty($deal->account->account_users)) {
            foreach ($deal->account->account_users as $account_user) {
                $account_user->user->notify(new NewDealNotification($deal, $deal->account));
            }
        }

        if (isset($deal->account->slack_webhook_url)) {
            Notification::route('slack', $deal->account->slack_webhook_url)->notify(new NewDealNotification($deal,
                $deal->account, true));
        }
    }
}
