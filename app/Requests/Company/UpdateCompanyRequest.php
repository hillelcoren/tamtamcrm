<?php

namespace App\Requests\Company;

use App\Repositories\Base\BaseFormRequest;

class UpdateCompanyRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => ['required'],
            'website' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'email' => ['required', 'string'],
            'address_1' => ['required', 'string'],
            'industry_id' => 'integer|nullable',
            'country_id' => 'integer|nullable',
            'city' => ['required', 'string'],
            'town' => ['required', 'string'],
            'postcode' => ['required', 'string'],
            //'company_logo' => 'mimes:jpeg,jpg,png,gif|max:10000|nullable' // max 10000kb
        ];

        $rules['contacts.*.email'] = 'nullable|distinct';

        $contacts = request('contacts');

        if (is_array($contacts)) {
            // for ($i = 0; $i < count($contacts); $i++) {
            // //    $rules['contacts.' . $i . '.email'] = 'nullable|email|unique:client_contacts,email,' . isset($contacts[$i]['id'].',company_id,'.$this->company_id);
            //     //$rules['contacts.' . $i . '.email'] = 'nullable|email';
            // }
        }
        return $rules;
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

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
