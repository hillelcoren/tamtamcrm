<?php

namespace App\Events\Deal;

use App\Account;
use App\Task;
use Illuminate\Queue\SerializesModels;


/**
 * Class PaymentWasCreated.
 */
class DealWasCreated
{
    use SerializesModels;
    /**
     * @var array $payment
     */
    public $deal;

    public $account;

    /**
     * DealWasCreated constructor.
     * @param Task $deal
     */
    public function __construct(Task $deal, Account $account)
    {
        $this->deal = $deal;
        $this->account = $account;
    }
}
