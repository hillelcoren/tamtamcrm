<?php

namespace App\Factory;

use App\RecurringInvoice;

class RecurringInvoiceFactory
{
    public static function create(int $customer_id, int $account_id, $total): RecurringInvoice
    {
        $invoice = new RecurringInvoice();
        $invoice->account_id = $account_id;
        $invoice->customer_id = $customer_id;
        $invoice->status_id = RecurringInvoice::STATUS_DRAFT;
        $invoice->sub_total = 0;
        $invoice->tax_total = 0;
        $invoice->discount_total = 0;
        $invoice->is_amount_discount = true;
        $invoice->po_number = '';
        $invoice->number = '';
        $invoice->footer = '';
        $invoice->terms = '';
        $invoice->public_notes = '';
        $invoice->private_notes = '';
        $invoice->tax_rate_name = '';
        $invoice->tax_rate = 0;
        $invoice->date = null;
        $invoice->due_date = null;
        $invoice->is_deleted = false;
        $invoice->line_items = json_encode([]);
        $invoice->total = $total;
        $invoice->balance = $total;
        $invoice->partial = 0;
        $invoice->user_id = auth()->user()->id;
        $invoice->frequency_id = RecurringInvoice::FREQUENCY_MONTHLY;
        $invoice->start_date = null;
        $invoice->last_sent_date = null;
        $invoice->next_send_date = null;
        $invoice->remaining_cycles = 0;
        $invoice->custom_value1 = '';
        $invoice->custom_value2 = '';
        $invoice->custom_value3 = '';
        $invoice->custom_value4 = '';
        return $invoice;
    }
}
