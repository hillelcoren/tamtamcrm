<?php

namespace App\Services\Invoice;

use App\Events\Invoice\InvoiceWasEmailed;
use App\Helpers\Email\InvoiceEmail;
use App\Invoice;
use App\Jobs\Invoice\EmailInvoice;
use App\Services\AbstractService;
use Illuminate\Support\Carbon;

class SendEmail extends AbstractService
{
    private $invoice;
    private $reminder_template;
    private $contact;

    public function __construct($invoice, $reminder_template = null, $contact = null)
    {
        $this->invoice = $invoice;
        $this->reminder_template = $reminder_template;
        $this->contact = $contact;
    }

    /**
     * Builds the correct template to send
     * @param string $reminder_template The template name ie reminder1
     * @return array
     */
    public function run()
    {
        if (!$this->reminder_template) {
            $this->reminder_template = $this->invoice->status_id == Invoice::STATUS_DRAFT ||
            Carbon::parse($this->invoice->due_date) > now() ? 'invoice' : $this->invoice->calculateTemplate();
        }

        $this->invoice->invitations->each(function ($invitation) {
            $email_builder = (new InvoiceEmail())->build($invitation, $this->reminder_template);
            if ($invitation->contact->send && $invitation->contact->email) {
                EmailInvoice::dispatch($email_builder, $invitation);
            }
        });

        if ($this->invoice->invitations->count() > 0) {
            event(new InvoiceWasEmailed($this->invoice->invitations->first()));
        }
    }
}
