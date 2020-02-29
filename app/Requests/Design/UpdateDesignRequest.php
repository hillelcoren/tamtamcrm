<?php

namespace App\Requests\Design;

use App\DataMapper\CustomerSettings;
use App\Repositories\Base\BaseFormRequest;
use App\Rules\ValidClientGroupSettingsRule;

class UpdateDesignRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'design' => 'required',
        ];
    }
}
