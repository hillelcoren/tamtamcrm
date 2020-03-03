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
            'name' => ['required'],
            'contacts.*.email' => ['nullable', 'distinct'],
            'contacts.*.password' => [
                'sometimes',
                'string',
                'min:10',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();
        $cleaned_contacts = [];

        foreach ($input['contacts'] as $key => $contact) {
            if (isset($contact['password'])) {
                $contact['password'] = str_replace("*", "", $contact['password']);

                if (strlen($contact['password']) == 0) {
                    unset($input['contacts'][$key]['password']);
                }
            }

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
