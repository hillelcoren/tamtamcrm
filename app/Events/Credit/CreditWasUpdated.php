<?php

namespace App\Events\Credit;

use App\Account;
use App\Credit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditWasUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $credit;

    public $account;

    public function __construct(Credit $credit, Account $account)
    {
        $this->invoice = $credit;
        $this->account = $account;
    }
}
