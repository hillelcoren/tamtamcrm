<?php

namespace App\Factory;

use App\Customer;
use App\Invoice;
use App\RecurringInvoice;

class RecurringInvoiceToInvoiceFactory
{
    public static function create(RecurringInvoice $recurring_invoice, Customer $customer): Invoice
    {
        $invoice = new Invoice();
        $invoice->status_id = Invoice::STATUS_DRAFT;
        $invoice->sub_total = $recurring_invoice->sub_total;
        $invoice->tax_total = $recurring_invoice->tax_total;
        $invoice->discount_total = $recurring_invoice->discount_total;
        $invoice->is_amount_discount = $recurring_invoice->is_amount_discount;
        $invoice->po_number = $recurring_invoice->po_number;
        $invoice->footer = $recurring_invoice->footer;
        $invoice->terms = $recurring_invoice->terms;
        $invoice->public_notes = $recurring_invoice->public_notes;
        $invoice->private_notes = $recurring_invoice->private_notes;
        $invoice->date = date_create()->format($customer->date_format());
        $invoice->due_date = $recurring_invoice->due_date; //todo calculate based on terms
        $invoice->is_deleted = $recurring_invoice->is_deleted;
        $invoice->line_items = $recurring_invoice->line_items;
        $invoice->total = $recurring_invoice->total;
        $invoice->balance = $recurring_invoice->balance;
        $invoice->user_id = $recurring_invoice->user_id;
        $invoice->recurring_id = $recurring_invoice->id;
        $invoice->customer_id = $recurring_invoice->customer_id;

        return $invoice;
    }
}
