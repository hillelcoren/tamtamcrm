<?php

namespace App\Factory;

use App\Credit;
use App\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreditFactory
{
    public static function create(int $account_id,
        int $user_id,
        $total,
        Customer $customer,
        object $settings = null): Credit
    {
        $credit = new Credit;
        $credit->status_id = Credit::STATUS_DRAFT;
        $credit->balance = $total;
        $credit->customer_id = $customer->id;
        $credit->account_id = $account_id;
        $credit->total = $total;
        $credit->user_id = $user_id;
        $credit->footer = isset($settings) && strlen($settings->credit_footer) > 0 ? $settings->credit_footer : '';
        $credit->terms = isset($settings) && strlen($settings->credit_terms) > 0 ? $settings->credit_terms : '';
        $credit->public_notes = isset($customer) && strlen($customer->public_notes) > 0 ? $customer->public_notes : '';
        $credit->private_notes = '';
        $credit->tax_rate_name = '';
        $credit->tax_rate = 0;
        return $credit;
    }
}
