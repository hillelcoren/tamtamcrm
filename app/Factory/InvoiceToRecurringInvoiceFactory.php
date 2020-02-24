<?php

namespace App\Factory;

use App\DataMapper\CustomerSettings;
use App\Invoice;
use App\RecurringInvoice;

class InvoiceToRecurringInvoiceFactory
{
    public static function create(Invoice $invoice): RecurringInvoice
    {
        $recurring_invoice = new RecurringInvoice;
        $recurring_invoice->status_id = RecurringInvoice::STATUS_DRAFT;
        $recurring_invoice->discount_total = $invoice->discount_total;
        $recurring_invoice->tax_total = $invoice->tax_total;
        $recurring_invoice->number = '';
        $recurring_invoice->is_amount_discount = $invoice->is_amount_discount;
        $recurring_invoice->po_number = $invoice->po_number;
        $recurring_invoice->footer = $invoice->footer;
        $recurring_invoice->terms = $invoice->terms;
        $recurring_invoice->public_notes = $invoice->public_notes;
        $recurring_invoice->private_notes = $invoice->private_notes;
        $recurring_invoice->tax_rate_name = $invoice->tax_rate_name;
        $recurring_invoice->tax_rate = $invoice->tax_rate;
        $recurring_invoice->date = date_create()->format('Y-m-d');
        $recurring_invoice->due_date = $invoice->due_date; //todo calculate based on terms
        $recurring_invoice->is_deleted = $invoice->is_deleted;
        $recurring_invoice->line_items = $invoice->line_items;
        $recurring_invoice->custom_value1 = $invoice->custom_value1;
        $recurring_invoice->custom_value2 = $invoice->custom_value2;
        $recurring_invoice->custom_value3 = $invoice->custom_value3;
        $recurring_invoice->custom_value4 = $invoice->custom_value4;
        $recurring_invoice->total = $invoice->total;
        $recurring_invoice->sub_total = $invoice->sub_total;
        $recurring_invoice->balance = $invoice->balance;
        $recurring_invoice->user_id = $invoice->user_id;
        $recurring_invoice->customer_id = $invoice->customer_id;
        $recurring_invoice->account_id = $invoice->account_id;
        $recurring_invoice->frequency_id = RecurringInvoice::FREQUENCY_MONTHLY;
        $recurring_invoice->start_date = null;
        $recurring_invoice->last_sent_date = null;
        $recurring_invoice->next_send_date = null;
        $recurring_invoice->remaining_cycles = 0;
        return $recurring_invoice;
    }
}
