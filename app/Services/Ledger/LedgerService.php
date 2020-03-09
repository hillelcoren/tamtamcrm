<?php

namespace App\Services\Ledger;

use App\CompanyLedger;
use App\Factory\CompanyLedgerFactory;

class LedgerService
{

    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function updateInvoiceBalance($adjustment)
    {
        $balance = 0;

        $company_ledger = $this->ledger();

        if ($company_ledger) {
            $balance = $company_ledger->balance;
        }

        $company_ledger = CompanyLedgerFactory::create($this->entity->account_id, $this->entity->user_id);
        $company_ledger->customer_id = $this->entity->customer_id;
        $company_ledger->adjustment = $adjustment;
        $company_ledger->balance = $balance + $adjustment;
        $company_ledger->save();

        $this->entity->company_ledger()->save($company_ledger);

        return $this;
    }

    public function updatePaymentBalance($adjustment)
    {
        $balance = 0;

        /* Get the last record for the client and set the current balance*/
        $company_ledger = $this->ledger();

        if ($company_ledger) {
            $balance = $company_ledger->balance;
        }


        $company_ledger = CompanyLedgerFactory::create($this->entity->account_id, $this->entity->user_id);
        $company_ledger->customer_id = $this->entity->customer_id;
        $company_ledger->adjustment = $adjustment;
        $company_ledger->balance = $balance + $adjustment;
        $company_ledger->save();

        $this->entity->company_ledger()->save($company_ledger);
    }

    private function ledger(): ?CompanyLedger
    {

        return CompanyLedger::whereCustomerId($this->entity->client_id)->whereAccountId($this->entity->company_id)
                            ->orderBy('id', 'DESC')->first();

    }

    public function save()
    {

        $this->entity->save();

        return $this->entity;

    }

}
