<?php

namespace App\Listeners\Invoice;

use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateInvoiceActivity implements ShouldQueue
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
        $fields['data']['id'] = $event->invoice->id;
        $fields['data']['message'] = 'A new invoice was created';
        $fields['notifiable_id'] = $event->invoice->user_id;
        $fields['account_id'] = $event->invoice->account_id;
        $fields['notifiable_type'] = get_class($event->invoice);
        $fields['type'] = get_class($this);
        $fields['data'] = json_encode($fields['data']);
        $this->notification_repo->create($fields);

    }
}
