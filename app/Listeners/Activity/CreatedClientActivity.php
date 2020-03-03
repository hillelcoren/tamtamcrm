<?php

namespace App\Listeners\Activity;

use App\Factory\NotificationFactory;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreatedClientActivity implements ShouldQueue
{
    protected $notification_repo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(NotificationRepository $notification_repo)
    {
        $this->notification_repo = $notification_repo;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $fields = [];
        $fields['data']['id'] = $event->client->id;
        $fields['data']['message'] = 'A new customer was created';
        $fields['notifiable_id'] = $event->client->user_id;
        $fields['account_id'] = $event->client->account_id;
        $fields['notifiable_type'] = get_class($event->client);
        $fields['type'] = get_class($this);
        $fields['data'] = json_encode($fields['data']);

        $notification = NotificationFactory::create($event->client->account_id, $event->client->user_id);
        $this->notification_repo->save($notification, $fields);
    }
}
