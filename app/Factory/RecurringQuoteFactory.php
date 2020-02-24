<?php

namespace App\Factory;

use App\RecurringQuote;

class RecurringQuoteFactory
{
    public static function create(int $customer_id, int $account_id, int $total): RecurringQuote
    {
        $quote = new RecurringQuote();
        $quote->account_id = $account_id;
        $quote->status_id = RecurringQuote::STATUS_DRAFT;
        $quote->discount = 0;
        $quote->is_amount_discount = true;
        $quote->po_number = '';
        $quote->footer = '';
        $quote->terms = '';
        $quote->public_notes = '';
        $quote->private_notes = '';
        $quote->date = null;
        $quote->due_date = null;
        $quote->is_deleted = false;
        $quote->tax_rate_name = '';
        $quote->tax_rate = 0;
        $quote->line_items = json_encode([]);
        $quote->total = $total;
        $quote->balance = $total;
        $quote->user_id = auth()->user()->id;
        $quote->customer_id = $customer_id;
        $quote->frequency_id = RecurringQuote::FREQUENCY_MONTHLY;
        $quote->start_date = null;
        $quote->last_sent_date = null;
        $quote->next_send_date = null;
        $quote->remaining_cycles = 0;
        $quote->custom_value1 = '';
        $quote->custom_value2 = '';
        $quote->custom_value3 = '';
        $quote->custom_value4 = '';
        return $quote;
    }
}
