<?php

namespace App\Jobs\Credit;


use App\Jobs\Credit\ApplyPaymentToCredit;
use App\Credit;
use App\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyCreditPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $credit;
    public $payment;
    public $amount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Credit $credit, Payment $payment, float $amount)
    {
        $this->credit = $credit;
        $this->payment = $payment;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     *
     * @return void
     */
    public function handle()
    {
        /* Update Pivot Record amount */
        $this->payment->credits->each(function ($cred) {
            if ($cred->id == $this->credit->id) {
                $cred->pivot->amount = $this->amount;
                $cred->pivot->save();
            }
        });
        $credit_balance = $this->credit->balance;
        if ($this->amount == $credit_balance) { //total credit applied.
            $this->credit->setStatus(Credit::STATUS_APPLIED);
            $this->credit->updateBalance($this->amount * -1);
        } elseif ($this->amount < $credit_balance) { //compare number appropriately
            $this->credit->setStatus(Credit::PARTIAL);
            $this->credit->updateBalance($this->amount * -1);
        }

        /* Update Payment Applied Amount*/
        $this->payment->save();
    }

}
