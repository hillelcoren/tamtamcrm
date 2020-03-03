<?php

namespace App\Requests\Design;

use App\DataMapper\CustomerSettings;
use App\Repositories\Base\BaseFormRequest;
use App\Rules\ValidClientGroupSettingsRule;

class StoreDesignRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //'name' => 'required',
            'name' => 'required|unique:designs,name,null,null,account_id,' . auth()->user()->account_user()->account_id,
            'design' => 'required',
        ];
    }
}
