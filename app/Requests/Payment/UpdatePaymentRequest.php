<?php

namespace App\Requests\Payment;

use App\Repositories\Base\BaseFormRequest;
use App\Rules\PaymentAppliedValidAmount;
use App\Rules\ValidCreditsPresentRule;

class UpdatePaymentRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoices' => ['required', 'array', 'min:1', new PaymentAppliedValidAmount, new ValidCreditsPresentRule],
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if (array_key_exists('customer_id', $input)) {
            unset($input['customer_id']);
        }

        if (array_key_exists('amount', $input)) {
            unset($input['amount']);
        }

        if (array_key_exists('type_id', $input)) {
            unset($input['type_id']);
        }

        if (array_key_exists('date', $input)) {
            unset($input['date']);
        }

        if (array_key_exists('transaction_reference', $input)) {
            unset($input['transaction_reference']);
        }

        if (array_key_exists('number', $input)) {
            unset($input['number']);
        }

        foreach ($input['invoices'] as $key => $invoice) {
            if (empty($invoice['invoice_id']) || empty($invoice['amount'])) {
                unset($input['invoices'][$key]);
            }
        }

        $this->replace($input);
    }

}
