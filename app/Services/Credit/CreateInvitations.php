<?php

namespace App\Services\Credit;

use App\Credit;
use App\CreditInvitation;
use App\Factory\CreditInvitationFactory;
use App\Services\AbstractService;

class CreateInvitations extends AbstractService
{
    private $credit;

    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
    }

    public function run()
    {

        $contacts = $this->credit->customer->contacts;

        $contacts->each(function ($contact) {
            $invitation =
                CreditInvitation::whereAccountId($this->credit->account_id)->whereClientContactId($contact->id)
                                ->whereCreditId($this->credit->id)->first();

            if (!$invitation && $contact->send_email) {
                $ii = CreditInvitationFactory::create($this->credit->account_id, $this->credit->user_id);
                $ii->credit_id = $this->credit->id;
                $ii->client_contact_id = $contact->id;
                $ii->save();
            } elseif ($invitation && !$contact->send_email) {
                $invitation->delete();
            }
        });

        return $this->credit;
    }
}
