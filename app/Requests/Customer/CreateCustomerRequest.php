<?php

namespace App\Requests\Customer;

use App\DataMapper\CustomerSettings;
use App\Repositories\Base\BaseFormRequest;
use App\Rules\ValidClientGroupSettingsRule;

class CreateCustomerRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'settings' => new ValidClientGroupSettingsRule(),
            'customer_type' => 'required',
            //            'address_1' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:customers'],
            'contacts.*.email' => 'nullable|distinct'
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if (!isset($input['settings'])) {
            $input['settings'] = CustomerSettings::defaults();
        }

        $this->replace($input);
    }

    public function messages()
    {
        return [
            'unique' => trans('validation.unique', ['attribute' => 'email']),
            //'required' => trans('validation.required', ['attribute' => 'email']),
            'contacts.*.email.required' => trans('validation.email', ['attribute' => 'email']),
        ];
    }
}
