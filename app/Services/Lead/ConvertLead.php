<?php

namespace App\Services\Lead;

use App\Factory\Lead\CloneLeadToAddressFactory;
use App\Factory\Lead\CloneLeadToContactFactory;
use App\Factory\Lead\CloneLeadToCustomerFactory;
use App\Factory\Lead\CloneLeadToTaskFactory;
use App\Lead;
use App\Services\AbstractService;
use App\Task;

/**
 * Class ConvertLead
 * @package App\Services\Task
 */
class ConvertLead extends AbstractService
{
    private $lead;

    /**
     * ConvertLead constructor.
     * @param Task $task
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function run()
    {
        $customer = CloneLeadToCustomerFactory::create($this->lead, $this->lead->user_id, $this->lead->account_id);
        $customer->save();

        $address = CloneLeadToAddressFactory::create($this->lead, $customer);
        $address->save();

        $client_contact = CloneLeadToContactFactory::create($this->lead, $customer, $this->lead->user_id,
            $this->lead->account_id);
        $client_contact->save();

        $task = CloneLeadToTaskFactory::create($this->lead, $customer, $this->lead->user_id, $this->lead->account_id);


        $date = new \DateTime(); // Y-m-d
        $date->add(new \DateInterval('P30D'));
        $due_date = $date->format('Y-m-d');

        $task->due_date = $due_date;
        $task->save();

        return $this->lead;
    }
}
