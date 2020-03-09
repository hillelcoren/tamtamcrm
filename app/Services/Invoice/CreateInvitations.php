<?php

namespace App\Services\Invoice;


use App\Factory\InvoiceInvitationFactory;
use App\Invoice;
use App\InvoiceInvitation;
use App\Services\AbstractService;

class CreateInvitations extends AbstractService
{
    private $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function run()
    {

        $contacts = $this->invoice->customer->contacts;

        $contacts->each(function ($contact) {
            $invitation =
                InvoiceInvitation::whereAccountId($this->invoice->account_id)->whereClientContactId($contact->id)
                                 ->whereInvoiceId($this->invoice->id)->first();

            if (!$invitation && $contact->send_email) {
                $ii = InvoiceInvitationFactory::create($this->invoice->account_id, $this->invoice->user_id);
                $ii->invoice_id = $this->invoice->id;
                $ii->client_contact_id = $contact->id;
                $ii->save();
            } elseif ($invitation && !$contact->send_email) {
                $invitation->delete();
            }
        });

        return $this->invoice;
    }
}
