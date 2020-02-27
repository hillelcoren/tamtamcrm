<?php

namespace App\Transformations;

use App\Address;
use App\ClientContact;
use App\Customer;
use App\Lead;

trait LeadTransformable
{
    protected function transformLead(Lead $lead)
    {

        $prop = new Lead;
        $prop->id = (int)$lead->id;
        $prop->created_at = $lead->created_at;
        $prop->first_name = $lead->first_name;
        $prop->last_name = $lead->last_name;
        $prop->title = $lead->title;
        $prop->description = $lead->description;
        $prop->valued_at = $lead->valued_at;
        $prop->source_type = $lead->source_type;
        $prop->task_status = $lead->task_status;
        $prop->address_1 = $lead->address_1;
        $prop->address_2 = $lead->address_2;
        $prop->city = $lead->city;
        $prop->zip = $lead->zip;
        $prop->email = $lead->email;
        $prop->phone = $lead->phone;
        $prop->company_name = $lead->company_name;
        $prop->job_title = $lead->job_title;
        $prop->deleted_at = $lead->deleted_at;
        $prop->website = $lead->website ?: '';
        $prop->private_notes = $lead->private_notes ?: '';
        $prop->public_notes = $lead->public_notes ?: '';

        return $prop;
    }
}
