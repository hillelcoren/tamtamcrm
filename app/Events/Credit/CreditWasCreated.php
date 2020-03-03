<?php

namespace App\Events\Credit;

use App\Account;
use App\Credit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditWasCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $credit;

    public $account;

    /**
     * Create a new event instance.
     *
     * @param Credit $credit
     */
    public function __construct(Credit $credit, Account $account)
    {
        $this->credit = $credit;
        $this->account = $account;
    }
}
