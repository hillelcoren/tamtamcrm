<?php

namespace App\Services\Payment;

use App\Factory\PaymentFactory;
use App\Invoice;
use App\Payment;

class PaymentService
{
    private $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function manualPayment($invoice): ?Payment
    {
        /* Create Payment */
        $payment = PaymentFactory::create($invoice->customer_id, $invoice->user_id, $invoice->account_id);

        $payment->amount = $invoice->balance;
        $payment->status_id = Payment::STATUS_COMPLETED;
        $payment->customer_id = $invoice->customer_id;
        $payment->transaction_reference = trans('texts.manual_entry');
        /* Create a payment relationship to the invoice entity */
        $payment->save();

        $payment->invoices()->attach($invoice->id, [
            'amount' => $payment->amount
        ]);

        return $payment;
    }

    public function sendEmail($contact = null)
    {
        return (new SendEmail($this->payment, $contact))->run();
    }

    public function reversePayment()
    {
        $invoices = $this->payment->invoices()->get();
        $customer = $this->payment->customer;

        $invoices->each(function ($invoice) {
            if ($invoice->pivot->amount > 0) {
                $invoice->status_id = Invoice::STATUS_SENT;
                $invoice->balance = $invoice->pivot->amount;
                $invoice->save();
            }
        });

        $this->payment->ledger()->updatePaymentBalance($this->payment->amount);

        $customer->service()->updateBalance($this->payment->amount)->updatePaidToDate($this->payment->amount * -1)
                 ->save();
    }

    public function updateInvoicePayment()
    {
        return ((new UpdateInvoicePayment($this->payment)))->run();
    }
}
