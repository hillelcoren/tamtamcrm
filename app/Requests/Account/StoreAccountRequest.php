<?php

namespace App\Requests\Account;

use App\DataMapper\CompanySettings;
use App\Rules\ValidSettingsRule;
use App\Repositories\Base\BaseFormRequest;

class StoreAccountRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [];

        $rules['company_logo'] = 'mimes:jpeg,jpg,png,gif|max:10000'; // max 10000kb
        $rules['settings'] = new ValidSettingsRule();

        if (isset($rules['portal_mode']) && ($rules['portal_mode'] == 'domain' || $rules['portal_mode'] == 'iframe')) {
            $rules['portal_domain'] = 'sometimes|url';
        } else {
            $rules['portal_domain'] = 'nullable|alpha_num';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if (array_key_exists('settings', $input) && property_exists($input['settings'], 'pdf_variables') &&
            empty((array)$input['settings']->pdf_variables)) {
            $input['settings']['pdf_variables'] = CompanySettings::getEntityVariableDefaults();
        }

        $this->replace($input);
    }
}
