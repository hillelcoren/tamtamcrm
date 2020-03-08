<?php

namespace App\Services\Invoice;

//use App\Jobs\Company\UpdateCompanyLedgerWithPayment;
use App\Invoice;
use App\Payment;
use App\Services\AbstractService;
use App\Services\Customer\CustomerService;

class ApplyPayment extends AbstractService
{

    private $invoice;
    private $payment;
    private $payment_amount;

    public function __construct($invoice, $payment, $payment_amount)
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
        $this->payment_amount = $payment_amount;
    }

    public function run()
    {
        $this->payment->ledger()->updatePaymentBalance($this->payment_amount * -1);

        $this->payment->customer->service()->updateBalance($this->payment_amount * -1)->save();

        /* Update Pivot Record amount */
        $this->payment->invoices->each(function ($inv) {
            if ($inv->id == $this->invoice->id) {
                $inv->pivot->amount = $this->payment_amount;
                $inv->pivot->save();
            }
        });

        if ($this->invoice->hasPartial()) {
            //is partial and amount is exactly the partial amount
            if ($this->invoice->partial == $this->payment_amount) {
                $this->invoice->service()->clearPartial()->setDueDate()->setStatus(Invoice::STATUS_PARTIAL)
                    ->updateBalance($this->payment_amount * -1);
            } elseif ($this->invoice->partial > 0 && $this->invoice->partial >
                $this->payment_amount
            ) { //partial amount exists, but the amount is less than the partial amount
                $this->invoice->service()->updatePartial($this->payment_amount * -1)
                    ->updateBalance($this->payment_amount * -1);
            } elseif ($this->invoice->partial > 0 && $this->invoice->partial <
                $this->payment_amount
            ) { //partial exists and the amount paid is GREATER than the partial amount
                $this->invoice->service()->clearPartial()->setDueDate()->setStatus(Invoice::STATUS_PARTIAL)
                    ->updateBalance($this->payment_amount * -1);
            }
        } elseif ($this->payment_amount == $this->invoice->balance) { //total invoice paid.
            $this->invoice->service()->clearPartial()->setStatus(Invoice::STATUS_PAID)
                ->updateBalance($this->payment_amount * -1);
        } elseif ($this->payment_amount < $this->invoice->balance) { //partial invoice payment made
            $this->invoice->service()->clearPartial()->updateBalance($this->payment_amount * -1);
        }

        return $this->invoice;
    }
}
