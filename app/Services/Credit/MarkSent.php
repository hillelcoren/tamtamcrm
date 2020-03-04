<?php

namespace App\Services\Credit;

use App\Credit;
use App\Customer;
use App\Events\Credit\CreditWasMarkedSent;
use App\Services\AbstractService;

class MarkSent
{
    private $client;

    private $credit;

    public function __construct(Customer $client, Credit $credit)
    {
        $this->client = $client;
        $this->credit = $credit;
    }

    public function run()
    {

        /* Return immediately if status is not draft */
        if ($this->credit->status_id != Credit::STATUS_DRAFT) {
            return $this->credit;
        }

        $this->credit->markInvitationsSent();

        event(new CreditWasMarkedSent($this->credit, $this->credit->account));

        $this->credit->service()->setStatus(Credit::STATUS_SENT)->applyNumber()->save();

        return $this->credit;

    }
}
