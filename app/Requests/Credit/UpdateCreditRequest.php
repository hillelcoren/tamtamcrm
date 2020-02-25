<?php

namespace App\Requests\Credit;

use App\Repositories\Base\BaseFormRequest;

class UpdateCreditRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'required',
            'total' => 'required|numeric',
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();


        if (isset($input['invitations'])) {

            foreach ($input['invitations'] as $key => $value) {
                if (is_numeric($input['invitations'][$key]['id'])) {
                    unset($input['invitations'][$key]['id']);
                }
            }
        }

        $input['line_items'] = isset($input['line_items']) ? $this->cleanItems($input['line_items']) : [];

        $this->replace($input);
    }
}
