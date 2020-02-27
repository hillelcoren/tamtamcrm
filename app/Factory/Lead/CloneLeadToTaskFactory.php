<?php

namespace App\Factory\Lead;

use App\Lead;
use App\Task;
use App\Customer;

class CloneLeadToTaskFactory
{
    public static function create(Lead $lead, Customer $customer, $user_id, $account_id): Task
    {
        $client_contact = new Task();
        $client_contact->account_id = $account_id;
        $client_contact->customer_id = $customer->id;
        $client_contact->user_id = $user_id;
        $client_contact->valued_at = $lead->valued_at;
        $client_contact->task_status = $lead->task_status;
        $client_contact->title = $lead->title;
        $client_contact->content = $lead->description;
        $client_contact->source_type = $lead->source_type;

        return $client_contact;
    }
}
