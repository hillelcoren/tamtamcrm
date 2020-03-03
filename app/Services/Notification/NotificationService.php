<?php

namespace App\Services\Notification;

use App\Models\Account;
use App\Services\AbstractService;
use Illuminate\Notifications\Notification as Notifiable;
use Illuminate\Support\Facades\Notification;

class NotificationService extends AbstractService
{

    public $account;

    public $notification;

    public function __construct(Account $account, Notifiable $notification)
    {

        $this->account = $account;

        $this->notification = $notification;

    }

    public function run($is_system = false)
    {

        $this->account->owner()->notify($this->notification);

        if ($is_system) {
            $this->notification->is_system = true;

            Notification::route('slack', $this->account->slack_webhook_url)->notify($this->notification);
        }

    }

    /**
     * Hosted notifications
     * @return void
     */
    public function ninja()
    {

        Notification::route('slack', config('ninja.notification.slack'))
                    ->route('mail', config('ninja.notification.mail'))->notify($this->notification);

    }
}
