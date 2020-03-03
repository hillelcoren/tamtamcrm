<?php

namespace App\Rules\Payment;

use App\Invoice;
use App\Utils\Traits\MakesHash;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ValidInvoicesRules
 * @package App\Http\ValidationRules\Payment
 */
class ValidInvoicesRules implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    private $error_msg;

    private $input;


    public function __construct($input)
    {
        $this->input = $input;
    }

    public function passes($attribute, $value)
    {

        return $this->checkInvoicesAreHomogenous();

    }

    private function checkInvoicesAreHomogenous()
    {

        if (!array_key_exists('customer_id', $this->input)) {
            $this->error_msg = "Customer id is required";
            return false;
        }

        $unique_array = [];

        foreach ($this->input['invoices'] as $invoice) {
            $unique_array[] = $invoice['invoice_id'];

            $inv = Invoice::whereId($invoice['invoice_id'])->first();

            if (!$inv) {
                $this->error_msg = "Invoice not found ";
                return false;
            }

            if ($inv->customer_id != $this->input['customer_id']) {
                $this->error_msg = "Selected invoices are not from a single client";
                return false;
            }
        }

        if (!(array_unique($unique_array) == $unique_array)) {
            $this->error_msg = "Duplicate invoices submitted.";
            return false;
        }

        return true;

    }


    /**
     * @return string
     */
    public function message()
    {
        return $this->error_msg;
    }
}
