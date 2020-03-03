<?php

namespace App\Services\Invoice;

use App\Customer;
use App\Events\Invoice\InvoiceWasMarkedSent;
use App\Jobs\Company\UpdateCompanyLedgerWithInvoice;
use App\Invoice;
use App\Services\AbstractService;

class MarkSent extends AbstractService
{
    private $customer;
    private $invoice;

    /**
     * MarkSent constructor.
     * @param Customer $customer
     * @param Invoice $invoice
     */
    public function __construct(Customer $customer, Invoice $invoice)
    {
        $this->customer = $customer;
        $this->invoice = $invoice;
    }

    public function run()
    {

        /* Return immediately if status is not draft */
        if ($this->invoice->status_id != Invoice::STATUS_DRAFT) {
            return $this->invoice;
        }

        $this->invoice->markInvitationsSent();

        $this->invoice->setReminder();

        event(new InvoiceWasMarkedSent($this->invoice, $this->invoice->account));

        $this->invoice->service()->setStatus(Invoice::STATUS_SENT)->applyNumber()->save();

        $this->customer->service()->updateBalance($this->invoice->balance)->save();

        $this->invoice->ledger()->updateInvoiceBalance($this->invoice->balance);

        return $this->invoice;

    }
}
