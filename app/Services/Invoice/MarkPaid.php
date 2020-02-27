<?php

namespace App\Services\Invoice;

use App\Events\Payment\PaymentWasCreated;
use App\Factory\PaymentFactory;
use App\Jobs\Customer\UpdateCustomerBalance;
use App\Jobs\Customer\UpdateCustomerPaidToDate;
//use App\Jobs\Company\UpdateCompanyLedgerWithPayment;
use App\Invoice;
use App\Payment;
use App\Services\AbstractService;
use App\Services\Customer\CustomerService;
use App\Services\Payment\PaymentService;
use App\Traits\GeneratesCounter;

class MarkPaid extends AbstractService
{
    use GeneratesCounter;

    private $customer_service;
    private $invoice;

    public function __construct($customer_service, $invoice)
    {
        $this->customer_service = $customer_service;
        $this->invoice = $invoice;
    }

    public function run()
    {

        if ($this->invoice->status_id == Invoice::STATUS_DRAFT) {
            $this->invoice->service()->markSent();
        }

        /* Create Payment */
        $payment =
            PaymentFactory::create($this->invoice->customer_id, $this->invoice->user_id, $this->invoice->account_id);

        $payment->amount = $this->invoice->balance;
        $payment->applied = $this->invoice->balance;
        $payment->number = $this->getNextPaymentNumber($this->invoice->customer);
        $payment->status_id = Payment::STATUS_COMPLETED;
        $payment->customer_id = $this->invoice->customer_id;
        $payment->transaction_reference = 'manual entry';
        /* Create a payment relationship to the invoice entity */
        $payment->save();

        $payment->invoices()->attach($this->invoice->id, [
            'amount' => $payment->amount
        ]);

        $this->invoice->service()->updateBalance($payment->amount * -1)->setStatus(Invoice::STATUS_PAID)->save();

        /* Update Invoice balance */
        event(new PaymentWasCreated($payment, $payment->account));

        $payment->ledger()->updatePaymentBalance($payment->amount * -1);

        $this->customer_service->updateBalance($payment->amount * -1)->updatePaidToDate($payment->amount)->save();

        return $this->invoice;
    }

}
