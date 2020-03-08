<?php

namespace App\Listeners\Lead;

use App\Notifications\Admin\NewLeadNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;

class LeadNotification implements ShouldQueue
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
        $lead = $event->lead;

        if (!empty($lead->account->account_users)) {
            foreach ($lead->account->account_users as $account_user) {
                $account_user->user->notify(new NewLeadNotification($lead, $lead->account));
            }
        }

        if (isset($lead->account->slack_webhook_url)) {
            Notification::route('slack', $lead->account->slack_webhook_url)->notify(new NewLeadNotification($lead,
                $lead->account, true));
        }
    }
}
