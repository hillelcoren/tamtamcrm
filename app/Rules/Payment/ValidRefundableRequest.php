<?php

namespace App\Rules\Payment;

use App\Credit;
use App\Invoice;
use App\Payment;
use App\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ValidRefundableRequest
 * @package App\Http\ValidationRules\Payment
 */
class ValidRefundableRequest implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    private $error_msg;

    private $input;


    public function __construct($input)
    {
        $this->input = $input;
    }

    public function passes($attribute, $value)
    {
        $payment = Payment::whereId($this->input['id'])->first();


        if (!$payment) {
            $this->error_msg = "Unable to retrieve specified payment";
            return false;
        }

        $request_invoices = request()->has('invoices') ? $this->input['invoices'] : [];
        $request_credits = request()->has('credits') ? $this->input['credits'] : [];

        /*foreach ($request_invoices as $key => $value) {
            $request_invoices[$key]['invoice_id'] = $value['invoice_id'];
        }

        foreach ($request_credits as $key => $value) {
            $request_credits[$key]['credit_id'] = $value['credit_id'];
        }*/

        if ($payment->invoices()->exists()) {
            foreach ($payment->invoices as $paymentable_invoice) {
                $this->checkInvoice($paymentable_invoice, $request_invoices);
            }
        }

//        if ($payment->credits()->exists()) {
//            foreach ($payment->credits as $paymentable_credit) {
//                $this->checkCredit($paymentable_credit, $request_credits);
//            }
//        }

        foreach ($request_invoices as $request_invoice) {
            $this->checkInvoiceIsPaymentable($request_invoice, $payment);
        }


//        foreach ($request_credits as $request_credit) {
//            $this->checkCreditIsPaymentable($request_credit, $payment);
//        }


        if (strlen($this->error_msg) > 0) {
            return false;
        }

        return true;
    }

    private function checkInvoiceIsPaymentable($invoice, $payment)
    {
        $invoice = Invoice::whereId($invoice['invoice_id'])->whereAccountId($payment->account_id)->first();

        if (empty($invoice)) {
            return true;
        }

        if ($payment->invoices()->exists()) {

            $paymentable_invoice = $payment->invoices->where('id', $invoice->id)->first();

            if (!$paymentable_invoice) {
                $this->error_msg = "Invoice id " . $invoice->id . " is not related to this payment";
                return false;
            }
        } else {
            $this->error_msg = "Invoice id " . $invoice->id . " is not related to this payment";
            return false;
        }
    }

    private function checkCreditIsPaymentable($credit, $payment)
    {
        $credit = Credit::find($credit['credit_id']);

        if ($payment->credits()->exists()) {

            $paymentable_credit = $payment->credits->where('id', $credit->id)->first();

            if (!$paymentable_credit) {
                $this->error_msg = "Credit id " . $credit->id . " is not related to this payment";
                return false;
            }
        } else {
            $this->error_msg = "Credit id " . $credit->id . " is not related to this payment";
            return false;
        }
    }

    private function checkInvoice($paymentable, $request_invoices)
    {
        $record_found = false;

        foreach ($request_invoices as $request_invoice) {

            if ($request_invoice['invoice_id'] == $paymentable->pivot->paymentable_id) {
                $record_found = true;

                $refundable_amount = ($paymentable->pivot->amount - $paymentable->pivot->refunded);

                if ($request_invoice['amount'] > $refundable_amount) {

                    $invoice = $paymentable;

                    $this->error_msg = "Attempting to refund more than allowed for invoice id " . $invoice->id .
                        ", maximum refundable amount is " . $refundable_amount;
                    return false;
                }
            }
        }

        if (!$record_found) {
            $this->error_msg =
                "Attempting to refund a payment with invoices attached, please specify valid invoice/s to be refunded.";
            return false;
        }
    }

    private function checkCredit($paymentable, $request_credits)
    {
        $record_found = null;

        foreach ($request_credits as $request_credit) {
            if ($request_credit['credit_id'] == $paymentable->pivot->paymentable_id) {

                $record_found = true;

                $refundable_amount = ($paymentable->pivot->amount - $paymentable->pivot->refunded);

                if ($request_credit['amount'] > $refundable_amount) {

                    $credit = $paymentable;

                    $this->error_msg = "Attempting to refund more than allowed for credit " . $credit->number .
                        ", maximum refundable amount is " . $refundable_amount;
                    return false;
                }

            }

        }

        if (!$record_found) {
            $this->error_msg =
                "Attempting to refund a payment with credits attached, please specify valid credit/s to be refunded.";
            return false;
        }

    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->error_msg;
    }
}
