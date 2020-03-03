<?php

namespace App\Listeners\Activity;

use App\Factory\NotificationFactory;
use App\Models\Activity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\ActivityRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentDeletedActivity implements ShouldQueue
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
        $payment = $event->payment;

        $invoices = $payment->invoices;

        $fields = [];
        $fields['data']['id'] = $payment->id;
        $fields['data']['message'] = 'A payment was deleted';
        $fields['notifiable_id'] = $payment->user_id;
        $fields['account_id'] = $payment->account_id;
        $fields['notifiable_type'] = get_class($payment);
        $fields['type'] = get_class($this);

        $notification = NotificationFactory::create($payment->account_id, $payment->user_id);

        foreach ($invoices as $invoice) { //todo we may need to add additional logic if in the future we apply payments to other entity Types, not just invoices
            $fields2 = $fields;

            $fields2['data']['invoice_id'] = $invoice->id;
            $fields2['data'] = json_encode($fields2['data']);
            $this->notification_repo->save($notification, $fields2);
        }

        if (count($invoices) == 0) {
            $fields['data'] = json_encode($fields['data']);
            $this->notification_repo->save($notification, $fields);
        }
    }
}
