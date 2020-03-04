<?php

namespace App\Traits\Payment;

use App\Factory\CreditFactory;
use App\Factory\InvoiceItemFactory;
use App\Credit;
use App\Factory\NotificationFactory;
use App\Invoice;
use App\Payment;

trait Refundable
{
    public function processRefund(array $data)
    {
        if (!empty($data['invoices'])) {
            return $this->refundPaymentWithInvoices($data);
        }

        return $this->refundPaymentWithNoInvoices($data);
    }

    private function refundPaymentWithNoInvoices(array $data)
    {
        //adjust payment refunded column amount
        $this->refunded = $data['amount'];

        if ($data['amount'] == $this->amount) {
            $this->status_id = Payment::STATUS_REFUNDED;
        } else {
            $this->status_id = Payment::STATUS_PARTIALLY_REFUNDED;
        }

        $credit_note = $this->buildCreditNote($data);

        $credit_line_item = InvoiceItemFactory::create();
        $credit_line_item->quantity = 1;
        $credit_line_item->cost = $data['amount'];
        $credit_line_item->product_key = 'CREDIT';
        $credit_line_item->notes = 'transaction_reference ' . $this->number;
        $credit_line_item->line_total = $data['amount'];
        $credit_line_item->date = $data['date'];

        $line_items = [];
        $line_items[] = $credit_line_item;

        $credit_note->save();

        $credit_note->number = $this->customer->getNextCreditNumber($this->customer);
        $credit_note->save();

        $this->createActivity($data, $credit_note->id);

        //determine if we need to refund via gateway
        if ($data['gateway_refund'] !== false) {
            //todo process gateway refund, on success, reduce the credit note balance to 0
        }

        $this->save();

        //$this->client->paid_to_date -= $data['amount'];
        $this->customer->save();

        return $this;
    }

    private function refundPaymentWithInvoices($data)
    {
        $total_refund = 0;

        foreach ($data['invoices'] as $invoice) {
            $total_refund += $invoice['amount'];
        }

        $data['amount'] = $total_refund;

        /* Set Payment Status*/
        if ($total_refund == $this->amount) {
            $this->status_id = Payment::STATUS_REFUNDED;
        } else {
            $this->status_id = Payment::STATUS_PARTIALLY_REFUNDED;
        }

        /* Build Credit Note*/
        $credit_note = $this->buildCreditNote($data);

        $line_items = [];

        foreach ($data['invoices'] as $invoice) {
            $inv = Invoice::find($invoice['invoice_id']);

            $credit_line_item = InvoiceItemFactory::create();
            $credit_line_item->quantity = 1;
            $credit_line_item->cost = $invoice['amount'];
            $credit_line_item->product_key = 'INVOICE';
            $credit_line_item->notes = 'REFUND ' . 'amount' . $data['amount'] . 'invoice_number' . $inv->number;
            $credit_line_item->line_total = $invoice['amount'];
            $credit_line_item->date = $data['date'];

            $line_items[] = $credit_line_item;
        }

        /* Update paymentable record */
        foreach ($this->invoices as $paymentable_invoice) {

            foreach ($data['invoices'] as $refunded_invoice) {

                if ($refunded_invoice['invoice_id'] == $paymentable_invoice->id) {
                    $paymentable_invoice->pivot->refunded += $refunded_invoice['amount'];
                    $paymentable_invoice->pivot->save();
                }
            }
        }

        if ($this->credits()->exists()) {
            //Adjust credits first!!!
            foreach ($this->credits as $paymentable_credit) {
                $available_credit = $paymentable_credit->pivot->amount - $paymentable_credit->pivot->refunded;

                if ($available_credit > $total_refund) {
                    $paymentable_credit->pivot->refunded += $total_refund;
                    $paymentable_credit->pivot->save();

                    $paymentable_credit->balance += $total_refund;
                    $paymentable_credit->save();

                    $total_refund = 0;

                } else {
                    $paymentable_credit->pivot->refunded += $available_credit;
                    $paymentable_credit->pivot->save();

                    $paymentable_credit->balance += $available_credit;
                    $paymentable_credit->save();

                    $total_refund -= $available_credit;
                }

                if ($total_refund == 0) {
                    break;
                }
            }
        }

        $credit_note->line_items = $line_items;
        $credit_note->save();

        $credit_note->number = $this->customer->getNextCreditNumber($this->customer);
        $credit_note->save();

        //determine if we need to refund via gateway
        if ($data['gateway_refund'] !== false) {
            //todo process gateway refund, on success, reduce the credit note balance to 0
        }


        if ($total_refund > 0) {
            $this->refunded += $total_refund;
        }

        $this->save();

        $this->adjustInvoices($data);

        $this->customer->paid_to_date -= $data['amount'];
        $this->customer->save();


        return $this;
    }


    /**
     * @param array $data
     * @param int $credit_id
     */
    private function createActivity(array $data, int $credit_id)
    {
        $notification = (new NotificationFactory())->create($this->account_id, $this->user_id);

        $fields = [
            'notifiable_type' => 'App\User',
            'notifiable_id' => $this->user_id,
            'type' => 'App\Notifications\RefundCreated'
        ];
        $fields['data'] = [
            'payment_id' => $this->id,
            'account_id' => $this->account_id,
            'credit_id' => $credit_id
        ];

        $fields['data'] = json_encode($fields['data']);

        if (!empty($data['invoices'])) {
            foreach ($data['invoices'] as $invoice) {
                $fields['data']['invoice_id'] = $invoice->id;

                $notification->fill($fields)->save();;

            }
        } else {
            $notification->fill($fields)->save();
        }
    }

    private function buildCreditNote(array $data): ?Credit
    {
        $credit_note = CreditFactory::create($this->account_id, $this->user_id, $this->customer);
        $credit_note->assigned_user_id = isset($this->assigned_user_id) ?: null;
        $credit_note->date = $data['date'];
        $credit_note->number = $this->getNextCreditNumber($this->customer);
        $credit_note->status_id = Credit::STATUS_SENT;
        $credit_note->customer_id = $this->customer->id;
        $credit_note->total = $data['amount'];
        $credit_note->balance = $data['amount'];

        return $credit_note;
    }

    private function adjustInvoices(array $data): void
    {
        foreach ($data['invoices'] as $refunded_invoice) {
            $invoice = Invoice::find($refunded_invoice['invoice_id']);

            $invoice->service()->updateBalance($refunded_invoice['amount'])->save();

            if ($invoice->amount == $invoice->balance) {
                $invoice->service()->setStatus(Invoice::STATUS_SENT);
            } else {
                $invoice->service()->setStatus(Invoice::STATUS_PARTIAL);
            }

            $customer = $invoice->customer;

            $customer->balance += $refunded_invoice['amount'];
            ///$client->paid_to_date -= $refunded_invoice['amount'];

            $customer->save();

            //todo adjust ledger balance here? or after and reference the credit and its total

        }
    }
}
