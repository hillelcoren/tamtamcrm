<?php

namespace App\Events\Credit;

use App\Account;
use App\Credit;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditWasMarkedSent.
 */
class CreditWasMarkedSent
{
    use SerializesModels;
    /**
     * @var Credit
     */
    public $credit;
    public $account;

    /**
     * CreditWasMarkedSent constructor.
     * @param Credit $credit
     * @param Account $account
     */
    public function __construct(Credit $credit, Account $account)
    {
        $this->credit = $credit;
        $this->account = $account;
    }
}
