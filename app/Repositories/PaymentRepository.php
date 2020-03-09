<?php

namespace App\Repositories;

use App\Customer;
use App\Jobs\Credit\ApplyCreditPayment;
use App\Repositories\Base\BaseRepository;
use App\Payment;
use App\Credit;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Invoice;
use App\Events\Payment\PaymentWasCreated;
use App\Traits\GeneratesCounter;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    use GeneratesCounter;

    /**
     * PaymentRepository constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
        $this->model = $payment;
    }

    /**
     *  Return the payment
     * @param int $id
     * @return Payment
     */
    public function findPaymentById(int $id): Payment
    {
        return $this->findOneOrFail($id);
    }

    /**
     * Return all the couriers
     *
     * @param string $order
     * @param string $sort
     * @return Collection|mixed
     */
    public function listPayments(array $columns = ['*'], string $order = 'id', string $sort = 'desc'): Collection
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deletePayment()
    {
        return $this->delete();
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Saves and updates a payment. //todo refactor to handle refunds and payments.
     *
     *
     * @param Request $request the request object
     * @param Payment $payment The Payment object
     * @return Object       Payment $payment
     */
    public function save(array $request, Payment $payment): ?Payment
    {
        if ($payment->amount >= 0) {
            return $this->applyPayment($request, $payment);
        }

        return $this->refundPayment($request, $payment);
    }

    /**
     * Handles a positive payment request
     * @param Request $request The request object
     * @param Payment $payment The $payment entity
     * @return Payment          The updated/created payment object
     */
    private function applyPayment(array $data, Payment $payment): ?Payment
    {
        //check currencies here and fill the exchange rate data if necessary
        if (!$payment->id) {
            $this->processExchangeRates($data, $payment);
        }

        $payment->fill($data);

        $payment->status_id = Payment::STATUS_COMPLETED;

        $payment->save();


        if (!$payment->number || strlen($payment->number) == 0) {
            $payment->number = $payment->customer->getNextPaymentNumber($payment->customer);
        }

        //we only ever update the ACTUAL amount of money transferred
        $payment->customer->service()->updatePaidToDate($payment->amount)->save();

        $invoice_totals = 0;
        $credit_totals = 0;

        if (array_key_exists('invoices', $data) && is_array($data['invoices'])) {

            $invoice_totals = array_sum(array_column($data['invoices'], 'amount'));

            $invoices = Invoice::whereIn('id', array_column($data['invoices'], 'invoice_id'))->get();

            $payment->invoices()->saveMany($invoices);

            foreach ($data['invoices'] as $paid_invoice) {
                $invoice = Invoice::whereId($paid_invoice['invoice_id'])->first();

                if ($invoice) {
                    $invoice->service()->applyPayment($payment, $paid_invoice['amount'])->save();
                }
            }
        } else {
            //payment is made, but not to any invoice, therefore we are applying the payment to the clients credit
            $payment->customer->processUnappliedPayment($payment->amount);
        }

        if (array_key_exists('credits', $data) && is_array($data['credits'])) {

            $credit_totals = array_sum(array_column($data['credits'], 'amount'));

            $credits = Credit::whereIn('id', array_column($data['credits'], 'credit_id'))->get();

            $payment->credits()->saveMany($credits);

            foreach ($data['credits'] as $paid_credit) {
                $credit = Credit::whereId($paid_credit['credit_id'])->first();

                if ($credit) {
                    ApplyCreditPayment::dispatchNow($credit, $payment, $paid_credit['amount']);
                }
            }

        }

        event(new PaymentWasCreated($payment, $payment->account));

        $invoice_totals -= $credit_totals;

        //$payment->amount = $invoice_totals; //creates problems when setting amount like this.

        if ($invoice_totals == $payment->amount) {
            $payment->applied += $payment->amount;
        } elseif ($invoice_totals < $payment->amount) {
            $payment->applied += $invoice_totals;
        }

        //UpdateInvoicePayment::dispatchNow($payment);
        $payment->save();

        return $payment->fresh();
    }

    /**
     * If the client is paying in a currency other than
     * the company currency, we need to set a record
     */
    private function processExchangeRates($data, $payment)
    {
        $client = Customer::find($data['customer_id']);

        $client_currency = $client->currency_id;
        $company_currency = $client->account->settings->currency_id;

        if ($company_currency != $client_currency) {
            $currency = $client->currency;

            $payment->exchange_rate = $currency->exchange_rate;
            $payment->exchange_currency_id = $client_currency;
        }

        return $payment;
    }

    private function refundPayment(array $request, Payment $payment): ?Payment
    {
        //temp variable to sum the total refund/credit amount
//        $invoice_total_adjustment = 0;
//        if(!empty($request['invoices']) && is_array($request['invoices'])){
//
//            foreach($request['invoices'] as $adjusted_invoice) {
//                $invoice = Invoice::whereId($adjusted_invoice['invoice_id'])->first();
//                $invoice_total_adjustment += $adjusted_invoice['amount'];
//                if(array_key_exists('credits', $adjusted_invoice)){
//
//                    //process and insert credit notes
//                    foreach($adjusted_invoice['credits'] as $credit){
//                        $credit = $this->credit_repo->save($credit, CreditFactory::create(auth()->user()->company()->id, auth()->user()->id), $invoice);
//                    }
//                }
//                else {
//                    //todo - generate Credit Note for $amount on $invoice - the assumption here is that it is a FULL refund
//                }
//            }
//            if($request['amount'] != $invoice_total_adjustment)
//                return 'Amount must equal the sum of invoice adjustments';
//        }
//        //adjust applied amount
//        $payment->applied += $invoice_total_adjustment;
//        //adjust clients paid to date
//        $customer = $payment->customer;
//        $customer->paid_to_date += $invoice_total_adjustment;
//
//        $payment->save();
//        $customer->save();
    }
}
