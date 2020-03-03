<?php

namespace App\Services\Customer;

use App\Customer;

class CustomerService
{
    private $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function updateBalance(float $amount)
    {
        $this->customer->balance += $amount;

        return $this;
    }

    public function updatePaidToDate(float $amount)
    {
        $this->customer->paid_to_date += $amount;

        return $this;
    }

    public function adjustCreditBalance(float $amount)
    {
        $this->customer->credit_balance += $amount;

        return $this;
    }

    public function save()
    {
        $this->customer->save();

        return $this->customer;
    }
}
