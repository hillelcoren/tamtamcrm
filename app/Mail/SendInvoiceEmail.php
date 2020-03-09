<?php

namespace App\Mail;

use App\Transformations\AddressTransformable;
use App\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoiceEmail extends Mailable
{

    use Queueable, SerializesModels, AddressTransformable;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var bool
     */
    protected $reminder;

    /**
     * @var array
     */
    protected $template;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var Proposal
     */
    protected $proposal;

    /**
     * Create a new message instance.
     *
     * @param Invoice $invoice
     * @param string $pdf
     * @param bool $reminder
     * @param mixed $pdfString
     */
    public function __construct(Invoice $invoice,
        $userId = false,
        $reminder = false,
        $template = false,
        $proposal = false)
    {
        $this->invoice = $invoice;
        $this->userId = $userId;
        $this->reminder = $reminder;
        $this->template = $template;
        $this->proposal = $proposal;
        $this->server = config('database.default');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('emails.customer.sendInvoiceToCustomer', $this->invoice->toArray());
    }

}
