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

    public function run()
    {

        $this->account->owner()->notify($this->notification);

    }
}
