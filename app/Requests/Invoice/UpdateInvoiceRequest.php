<?php

namespace App\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\CleanLineItems;

class UpdateInvoiceRequest extends FormRequest
{
    use CleanLineItems;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'required',
            'date' => 'required',
            'due_date' => 'required'
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if(isset($input['invitations']))
        {
            foreach($input['invitations'] as $key => $value)
            {
                if(is_numeric($input['invitations'][$key]['id'])) {
                    unset($input['invitations'][$key]['id']);
                }
            }
        }

        $input['line_items'] = isset($input['line_items']) ? $this->cleanItems($input['line_items']) : [];
        //$input['line_items'] = json_encode($input['line_items']);
        $this->replace($input);
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'comment.required' => 'You must enter a comment!',
            'task_id.required' => 'There was an unexpected error!',
            'user_id.required' => 'There was an unexpected error!',
        ];
    }
}
