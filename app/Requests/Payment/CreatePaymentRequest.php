<?php

namespace App\Requests\Payment;

use App\Repositories\Base\BaseFormRequest;
use App\Rules\Payment\ValidInvoicesRules;
use App\Rules\ValidCreditsPresentRule;
use App\Rules\ValidPayableInvoicesRule;
use App\Rules\PaymentAmountsBalanceRule;

class CreatePaymentRequest extends BaseFormRequest
{
    protected function prepareForValidation()
    {
        $input = $this->all();

        $invoices_total = 0;
        $credits_total = 0;

        $test = [];
        if (isset($input['invoices']) && is_array($input['invoices']) !== false) {
            foreach ($input['invoices'] as $key => $invoice) {
                if (!empty($invoice['invoice_id'])) {
                    $test['invoices'][$key]['invoice_id'] = $invoice['invoice_id'];
                    $test['invoices'][$key]['amount'] = $invoice['amount'];
                    $invoices_total += $invoice['amount'];
                }
            }

            $input['invoices'] = $test['invoices'];
        }

        if (isset($input['invoices']) && is_array($input['invoices']) === false) {
            $input['invoices'] = null;
        }

        if (isset($input['credits']) && is_array($input['credits']) !== false) {
            foreach ($input['credits'] as $key => $value) {
                if (array_key_exists('credit_id', $input['credits'][$key])) {
                    $input['credits'][$key]['credit_id'] = $value['credit_id'];
                    $credits_total += $value['amount'];
                }
            }
        }

        if (isset($input['credits']) && is_array($input['credits']) === false) {
            $input['credits'] = null;
        }

        if (!isset($input['amount']) || $input['amount'] == 0) {
            $input['amount'] = $invoices_total - $credits_total;
        }

        $input['is_manual'] = true;

        $this->replace($input);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'amount' => 'numeric|required',
            'amount' => [new PaymentAmountsBalanceRule(), new ValidCreditsPresentRule()],
            'date' => 'required',
            'customer_id' => 'bail|required|exists:customers,id',
            'invoices.*.invoice_id' => 'required|distinct|exists:invoices,id',
            'invoices.*.invoice_id' => new ValidInvoicesRules($this->all()),
            'invoices.*.amount' => 'required',
            'credits.*.credit_id' => 'required|exists:credits,id',
            'credits.*.amount' => 'required',
            'invoices' => new ValidPayableInvoicesRule(),
            'number' => 'nullable',
        ];

        return $rules;
    }
}
