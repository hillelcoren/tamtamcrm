<?php

namespace App\Rules;

use App\Invoice;
use App\User;
use App\Payment;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class PaymentAppliedValidAmount
 * @package App\Http\ValidationRules
 */
class PaymentAppliedValidAmount implements Rule
{

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->calculateAmounts();
    }

    /**
     * @return string
     */
    public function message()
    {
        return 'Insufficient applied amount remaining to cover payment.';
    }

    private function calculateAmounts(): bool
    {
        $payment = Payment::whereId(request()->segment(3))->first();

        if (!$payment) {
            return false;
        }

        $payment_amounts = 0;
        $invoice_amounts = 0;

        $payment_amounts = $payment->amount - $payment->applied;

        if (request()->input('credits') && is_array(request()->input('credits'))) {
            foreach (request()->input('credits') as $credit) {
                $payment_amounts += $credit['amount'];
            }
        }

        if (request()->input('invoices') && is_array(request()->input('invoices'))) {
            foreach (request()->input('invoices') as $invoice) {

                $invoice_amounts += $invoice['amount'];
            }
        }

        return $payment_amounts >= $invoice_amounts;

    }
}
