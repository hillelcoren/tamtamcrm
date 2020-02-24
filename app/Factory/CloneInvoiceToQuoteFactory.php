<?php

namespace App\Factory;

use App\Invoice;
use App\Quote;

class CloneInvoiceToQuoteFactory
{
    public static function create(Invoice $invoice, $user_id): ?Quote
    {
        $quote = new Quote();
        $quote->customer_id = $invoice->customer_id;
        $quote->user_id = $user_id;
        $quote->account_id = $invoice->account_id;
        $quote->discount = 0;
        $quote->is_amount_discount = $invoice->is_amount_discount;
        $quote->po_number = $invoice->po_number;
        $quote->is_deleted = false;
        $quote->tax_rate_name = $invoice->tax_rate_name;
        $quote->tax_rate = $invoice->tax_rate;
        $quote->footer = $invoice->footer;
        $quote->public_notes = $invoice->public_notes;
        $quote->private_notes = $invoice->private_notes;
        $quote->terms = $invoice->terms;
        $quote->tax_total = $invoice->tax_total;
        $quote->sub_total = $invoice->sub_total;
        $quote->discount_total = $invoice->discount_total;
        $quote->custom_value1 = $invoice->custom_value1;
        $quote->custom_value2 = $invoice->custom_value2;
        $quote->custom_value3 = $invoice->custom_value3;
        $quote->custom_value4 = $invoice->custom_value4;
        $quote->total = $invoice->total;
        $quote->balance = $invoice->balance;
        $quote->partial = $invoice->partial;
        $quote->partial_due_date = $invoice->partial_due_date;
        $quote->last_viewed = $invoice->last_viewed;
        $quote->status_id = Quote::STATUS_DRAFT;
        $quote->number = '';
        $quote->date = $invoice->date;
        $quote->due_date = $invoice->due_date;
        $quote->partial_due_date = null;
        $quote->balance = $invoice->total;
        $quote->line_items = $invoice->line_items;
        return $quote;
    }
}
