<?php

namespace App\Requests\Customer;

use App\Repositories\Base\BaseFormRequest;
use App\Rules\ValidClientGroupSettingsRule;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends BaseFormRequest
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
            'first_name' => ['required'],
            'last_name' => ['required'],
            //'email' => ['required', 'email', Rule::unique('customers')->ignore($this->segment(3))],
            'contacts.*.email' => ['nullable', 'distinct']
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();
        $cleaned_contacts = [];

        foreach ($input['contacts'] as $contact) {
            if (trim($contact['first_name']) !== '' && trim($contact['last_name']) !== '') {
                $cleaned_contacts[] = $contact;
            }
        }

        $input['contacts'] = $cleaned_contacts;
        $this->replace($input);
    }

    public function messages()
    {
        return [
            'unique' => trans('validation.unique', ['attribute' => 'email']),
            'email' => trans('validation.email', ['attribute' => 'email']),
            'name.required' => trans('validation.required', ['attribute' => 'name']),
            'required' => trans('validation.required', ['attribute' => 'email']),
        ];
    }
}
