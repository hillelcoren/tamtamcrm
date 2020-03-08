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

        $company_settings = CompanySettings::defaults();

        if (array_key_exists('settings', $input) && !empty($input['settings'])) {
            $settings = json_decode($input['settings'], true);

            if (is_array($settings) && !empty($settings)) {
                foreach ($settings as $key => $value) {
                    $company_settings->{$key} = $value;
                }
            }
        }

        $input['settings'] = $company_settings;
// \Log::error($input);

//         if(array_key_exists('settings', $input) && property_exists($input['settings'], 'pdf_variables') && empty((array) $input['settings']->pdf_variables))
//         {
//             $input['settings']['pdf_variables'] = CompanySettings::getEntityVariableDefaults();
//         }

        // $company_settings->invoice_design_id = $this->encodePrimaryKey(1);
        // $company_settings->quote_design_id = $this->encodePrimaryKey(1);
        // $company_settings->credit_design_id = $this->encodePrimaryKey(1);

        // $input['settings'] = $company_settings;
        $this->replace($input);
    }
}
