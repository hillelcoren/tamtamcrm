<?php

namespace App\Requests\Customer;

use App\DataMapper\CustomerSettings;
use App\GroupSetting;
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
            //            'address_1' => ['required'],
            'name' => ['required'],
            'contacts.*.email' => 'nullable|distinct',
            'contacts.*.password' => [
                'nullable',
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

        if (!isset($input['settings'])) {
            $input['settings'] = CustomerSettings::defaults();
        }

        if (empty($input['currency_id'])) {
            if (empty($input['group_settings_id'])) {
                $input['currency_id'] = auth()->user()->account_user()->account->settings->currency_id;
            } else {
                $group_settings = GroupSetting::find($input['group_settings_id']);

                if ($group_settings && property_exists($group_settings->settings, 'currency_id') &&
                    is_int($group_settings->settings->currency_id)) {
                    $input['currency_id'] = $group_settings->currency_id;
                } else {
                    $input['settings']->currency_id = auth()->user()->account_user()->account->settings->currency_id;
                }
            }
        }

        foreach ($input['contacts'] as $key => $contact) {

            //Filter the client contact password - if it is sent with ***** we should ignore it!
            if (isset($contact['password'])) {
                if (strlen($contact['password']) == 0) {
                    $input['contacts'][$key]['password'] = '';
                } else {
                    $contact['password'] = str_replace("*", "", $contact['password']);

                    if (strlen($contact['password']) == 0) {
                        unset($input['contacts'][$key]['password']);
                    }

                }

            }

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
