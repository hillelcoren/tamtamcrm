<?php
namespace App\Factory;

use App\Credit;
use App\Quote;

class CloneCreditToQuoteFactory
{
    public static function create(Credit $credit, $user_id) : ?Quote
    {
        $quote = new Quote();
        $quote->customer_id = $credit->customer_id;
        $quote->user_id = $user_id;
        $quote->account_id = $credit->account_id;
        $quote->discount = 0;
        $quote->is_deleted = false;
        $quote->footer = $credit->footer;
        $quote->notes = $credit->notes;
        $quote->terms = $credit->terms;
        $quote->custom_value1 = $credit->custom_value1;
        $quote->custom_value2 = $credit->custom_value2;
        $quote->custom_value3 = $credit->custom_value3;
        $quote->custom_value4 = $credit->custom_value4;
        $quote->total = $credit->total;
        $quote->balance = $credit->balance;
        $quote->partial = $credit->partial;
        $quote->partial_due_date = $credit->partial_due_date;
        $quote->last_viewed = $credit->last_viewed;

        $quote->status_id = Quote::STATUS_DRAFT;
        $quote->number = '';
        $quote->date = null;
        $quote->due_date = null;
        $quote->partial_due_date = null;
        $quote->line_items = $credit->line_items;

        return $quote;
    }
}