<?php

namespace App\Jobs\Company;

use App\Factory\CompanyLedgerFactory;
use App\CompanyLedger;
use App\Invoice;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateCompanyLedgerWithInvoice
{
    use Dispatchable;
    public $adjustment;
    public $invoice;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, float $adjustment)
    {
        $this->invoice = $invoice;
        $this->adjustment = $adjustment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $balance = 0;
        $ledger =
            CompanyLedger::whereCustomerId($this->invoice->customer_id)->whereAccountId($this->invoice->account_id)
                         ->orderBy('id', 'DESC')->first();
        if ($ledger) {
            $balance = $ledger->balance;
        }
        $adjustment = $balance + $this->adjustment;

        $company_ledger = CompanyLedgerFactory::create($this->invoice->account_id, $this->invoice->user_id);
        $company_ledger->customer_id = $this->invoice->customer_id;
        $company_ledger->adjustment = $this->adjustment;
        $company_ledger->balance = $balance + $this->adjustment;
        $company_ledger->save();
        $this->invoice->company_ledger()->save($company_ledger);
    }
}
