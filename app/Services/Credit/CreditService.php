<?php

namespace App\Services\Credit;

use App\Credit;

class CreditService
{
    protected $credit;


    public function __construct($credit)
    {
        $this->credit = $credit;
    }

    public function getCreditPdf($contact)
    {
        $get_invoice_pdf = new GetCreditPdf($this->credit, $contact);

        return $get_invoice_pdf->run();
    }

    /**
     * Applies the invoice number
     * @return $this InvoiceService object
     */
    public function applyNumber()
    {
        $apply_number = new ApplyNumber($this->credit->customer, $this->credit);

        $this->credit = $apply_number->run();

        return $this;
    }

    public function createInvitations()
    {
        $create_invitation = new CreateInvitations($this->credit);

        $this->credit = $create_invitation->run();

        return $this;
    }

    public function setStatus($status)
    {
        $this->credit->status_id = $status;

        return $this;
    }

    public function markSent()
    {
        $this->credit = (new MarkSent($this->credit->customer, $this->credit))->run();

        return $this;
    }

    /**
     * Saves the credit
     * @return Credit object
     */
    public function save(): ?Credit
    {
        $this->credit->save();
        return $this->credit;
    }
}
