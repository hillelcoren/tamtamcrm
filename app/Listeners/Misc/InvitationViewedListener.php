<?php

namespace App\Listeners\Misc;

use App\Notification;
use App\Notifications\Admin\EntityViewedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InvitationViewedListener implements ShouldQueue
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
        $entity_name = $event->entity;
        $invitation = $event->invitation;

        $notification = new EntityViewedNotification($invitation, $entity_name);

        foreach ($invitation->account->account_users as $company_user) {
            $company_user->user->notify($notification);
        }

        if (isset($invitation->account->slack_webhook_url)) {

            $notification->is_system = true;

            Notification::route('slack', $invitation->account->slack_webhook_url)->notify($notification);

        }
    }
}
