<?php

namespace App\Services\Task;

use App\Customer;
use App\Events\Invoice\QuoteWasMarkedSent;
use App\Events\Quote\QuoteWasMarkedApproved;
use App\Jobs\Company\UpdateCompanyLedgerWithInvoice;
use App\Services\AbstractService;
use App\Task;
use App\Quote;

class ConvertLead extends AbstractService
{
    private $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function run()
    {
        $customer = $this->task->customer;
        $this->task->service()->setTaskType(Task::TASK_TYPE_DEAL)->save();
        $customer->service()->setCustomerType(Customer::CUSTOMER_TYPE_WON)->save();

        return $this->task;
    }
}
