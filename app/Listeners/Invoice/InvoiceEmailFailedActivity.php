<?php

namespace App\Listeners\Invoice;

use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use stdClass;

class InvoiceEmailFailedActivity implements ShouldQueue
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

        $fields = new stdClass;

        $fields->invoice_id = $event->invoice->id;
        $fields->user_id = $event->invoice->user_id;
        $fields->account_id = $event->invoice->account_id;
        //$fields->activity_type_id = Activity::EMAIL_INVOICE_FAILED;

        $this->notification_repo->save($fields, $event->invoice);
    }
}
