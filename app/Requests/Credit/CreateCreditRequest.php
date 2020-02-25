<?php

namespace App\Requests\Credit;

use App\Repositories\Base\BaseFormRequest;
use App\Traits\CleanLineItems;

class CreateCreditRequest extends BaseFormRequest
{
    use CleanLineItems;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'total' => 'required',
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        /*if(isset($input['client_contacts']))
        {
          foreach($input['client_contacts'] as $key => $contact)
          {
            if(!array_key_exists('send_email', $contact) || !array_key_exists('id', $contact))
            {
              unset($input['client_contacts'][$key]);
            }
          }
        } */

        if (isset($input['invitations'])) {

            foreach ($input['invitations'] as $key => $value) {

                if (isset($input['invitations'][$key]['id']) && is_numeric($input['invitations'][$key]['id'])) {
                    unset($input['invitations'][$key]['id']);
                }
            }

        }

        $input['line_items'] = isset($input['line_items']) ? $this->cleanItems($input['line_items']) : [];
        //$input['line_items'] = json_encode($input['line_items']);
        $this->replace($input);
    }
}
