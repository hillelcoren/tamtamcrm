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
        Customer $customer): Credit
    {
        $credit = new Credit;
        $credit->status_id = Credit::STATUS_DRAFT;
        $credit->balance = 0;
        $credit->customer_id = $customer->id;
        $credit->account_id = $account_id;
        $credit->total = 0;
        $credit->user_id = $user_id;
        $credit->footer = '';
        $credit->terms = '';
        $credit->public_notes = '';
        $credit->private_notes = '';
        $credit->tax_rate_name = '';
        $credit->tax_rate = 0;
        return $credit;
    }
}
