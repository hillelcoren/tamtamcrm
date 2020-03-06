<?php

namespace App\Events\Lead;

use App\Account;
use App\Lead;
use Illuminate\Queue\SerializesModels;

/**
 * Class PaymentWasCreated.
 */
class LeadWasCreated
{
    use SerializesModels;
    /**
     * @var array $payment
     */
    public $lead;

    public $account;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     */
    public function __construct(Lead $lead, Account $account)
    {
        $this->lead = $lead;
        $this->account = $account;
    }
}
