<?php

namespace App\Requests\User;

use App\Repositories\Base\BaseFormRequest;
use App\Rules\NewUniqueUser;
use App\DataMapper\DefaultSettings;

class CreateUserRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'department' => 'nullable|numeric',
            'gender' => 'nullable|string',
            'job_description' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'dob' => 'nullable|string',
            'role' => 'nullable|array',
            //            'password' => [
            //                'required',
            //                'string',
            //                'min:10',             // must be at least 10 characters in length
            //                'regex:/[a-z]/',      // must contain at least one lowercase letter
            //                'regex:/[A-Z]/',      // must contain at least one uppercase letter
            //                'regex:/[0-9]/',      // must contain at least one digit
            //                'regex:/[@$!%*#?&]/', // must contain a special character
            //            ],
            'username' => ['required', 'string', 'unique:users'],
            'profile_photo' => 'nullable|string',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string:max:100',
            'email' => new NewUniqueUser(),
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if (isset($input['company_user'])) {

            foreach ($input['company_user'] as $key => $company_user) {

                if (!isset($company_user['is_admin'])) {
                    $input['company_user'][$key]['is_admin'] = false;
                }

                if (!isset($company_user['permissions'])) {
                    $input['company_user'][$key]['permissions'] = '';
                }

                if (!isset($company_user['settings'])) {
                    //$input['company_user']['settings'] = DefaultSettings::userSettings();
                    $input['company_user'][$key]['settings'] = null;
                }
            }
        }

        $this->replace($input);
    }
}
