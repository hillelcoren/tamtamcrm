<?php

namespace App\Requests\TaxRate;

use App\Repositories\Base\BaseFormRequest;

class CreateTaxRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:tax_rates,name,null,null,account_id,' .
                auth()->user()->account_user()->account_id,
            'rate' => 'required|numeric',
        ];
    }
}
