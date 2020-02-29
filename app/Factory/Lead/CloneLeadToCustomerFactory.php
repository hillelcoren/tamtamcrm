<?php

namespace App\Factory\Lead;

use App\ClientContact;
use App\Customer;
use App\Lead;

class CloneLeadToCustomerFactory
{
    public static function create(Lead $lead, $user_id, $account_id): Customer
    {
        $client_contact = new Customer();
        $client_contact->account_id = $account_id;
        $client_contact->user_id = $user_id;
        $client_contact->first_name = $lead->first_name;
        $client_contact->last_name = $lead->last_name;
        $client_contact->email = $lead->email;
        $client_contact->phone = $lead->phone;
        $client_contact->website = $lead->website;
        $client_contact->public_notes = $lead->public_notes;
        $client_contact->private_notes = $lead->private_notes;
        $client_contact->currency_id = 2;
        $client_contact->customer_type = 2;

        return $client_contact;
    }
}
