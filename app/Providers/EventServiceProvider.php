<?php

namespace App\Providers;

use App\Events\Client\ClientWasCreated;
use App\Events\Invoice\InvoiceWasCreated;
use App\Events\Invoice\InvoiceWasMarkedSent;
use App\Events\Invoice\InvoiceWasPaid;
use App\Events\Invoice\InvoiceWasUpdated;
use App\Events\Payment\PaymentWasCreated;
use App\Events\Payment\PaymentWasDeleted;
use App\Events\PaymentWasRefunded;
use App\Events\PaymentWasVoided;
use App\Listeners\Activity\CreatedClientActivity;
use App\Listeners\Activity\PaymentCreatedActivity;
use App\Listeners\Activity\PaymentDeletedActivity;
use App\Listeners\Activity\PaymentRefundedActivity;
use App\Listeners\Activity\PaymentVoidedActivity;
use App\Listeners\Invoice\CreateInvoiceActivity;
use App\Listeners\Invoice\CreateInvoiceHtmlBackup;
use App\Listeners\Invoice\UpdateInvoiceActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Clients
        ClientWasCreated::class => [
            CreatedClientActivity::class,
            // 'App\Listeners\SubscriptionListener@createdClient',
        ],
        //payments
        PaymentWasCreated::class => [
            PaymentCreatedActivity::class,
        ],
        PaymentWasDeleted::class => [
            PaymentDeletedActivity::class,
        ],
        PaymentWasRefunded::class => [
            PaymentRefundedActivity::class,
        ],
        PaymentWasVoided::class => [
            PaymentVoidedActivity::class,
        ],
        //Invoices
        InvoiceWasMarkedSent::class => [
            CreateInvoiceHtmlBackup::class,
        ],
        InvoiceWasUpdated::class => [
            UpdateInvoiceActivity::class
        ],
        InvoiceWasCreated::class => [
            CreateInvoiceActivity::class
        ],
        InvoiceWasPaid::class => [
            CreateInvoiceHtmlBackup::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
