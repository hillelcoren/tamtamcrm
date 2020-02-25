<?php

namespace App\Jobs\RecurringInvoice;

use App\Factory\RecurringInvoiceToInvoiceFactory;
use App\Invoice;
use App\RecurringInvoice;
use App\Traits\GeneratesCounter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;

class SendRecurring implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use GeneratesCounter;

    public $recurring_invoice;

    protected $db;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(RecurringInvoice $recurring_invoice)
    {
        $this->recurring_invoice = $recurring_invoice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {

        // Generate Standard Invoice
        $invoice = RecurringInvoiceToInvoiceFactory::create($this->recurring_invoice);
        $invoice->number = $this->getNextRecurringInvoiceNumber($this->recurring_invoice->customer);
        $invoice->status_id = Invoice::STATUS_SENT;
        $invoice->save();

        // Queue: Emails for invoice
        // foreach invoice->invitations

        // Fire Payment if auto-bill is enabled
        if ($this->recurring_invoice->settings->auto_bill) {
            //PAYMENT ACTION HERE TODO

            // Clean up recurring invoice object

            $this->recurring_invoice->remaining_cycles = $this->recurring_invoice->remainingCycles();
        }

        $this->recurring_invoice->last_sent_date = date('Y-m-d');

        if ($this->recurring_invoice->remaining_cycles != 0) {
            $this->recurring_invoice->next_send_date = $this->recurring_invoice->nextSendDate();
        } else {
            $this->recurring_invoice->setCompleted();
        }

        $this->recurring_invoice->save();
    }
}
