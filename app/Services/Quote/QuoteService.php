<?php
namespace App\Services\Quote;

use App\Quote;

class QuoteService
{
    protected $quote;

    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    public function createInvitations()
    {
        $create_invitation = new CreateInvitations();

        $this->quote = $create_invitation($this->quote);

        return $this;
    }

    public function markApproved($invoice_repo)
    {
        $mark_approved = new MarkApproved($this->quote->customer);

        $this->quote = $mark_approved($this->quote);

        if($this->quote->customer->getSetting('auto_convert_quote') === true) {
            $convert_quote = new ConvertQuote($this->quote->client, $invoice_repo);
            $this->quote = $convert_quote($this->quote);
        }

        return $this;
    }

    /**
     * Applies the invoice number
     * @return $this InvoiceService object
     */
    public function applyNumber()
    {
        $apply_number = new ApplyNumber($this->quote->customer);

        $this->quote = $apply_number($this->quote);

        return $this;
    }

    public function markSent()
    {
        $mark_sent = new MarkSent($this->quote->customer);

        $this->quote = $mark_sent($this->quote);

        return $this;
    }

    public function setStatus($status)
    {
        $this->quote->status_id = $status;

        return $this;
    }

    /**
     * Saves the quote
     * @return Quote|null
     */
    public function save() : ?Quote
    {
        $this->quote->save();
        return $this->quote;
    }

    public function getQuotePdf($contact)
    {
        $get_invoice_pdf = new GetQuotePdf();

        return $get_invoice_pdf($this->quote, $contact);
    }

    public function sendEmail($contact = null)
    {
        $send_email = new SendEmail($this->quote);

        return $send_email->run(null, $contact);
    }
}