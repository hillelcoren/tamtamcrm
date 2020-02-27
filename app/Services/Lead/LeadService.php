<?php

namespace App\Services\Lead;

use App\Lead;

/**
 * Class TaskService
 * @package App\Services\Task
 */
class LeadService
{
    protected $lead;

    /**
     * LeadService constructor.
     * @param Lead $lead
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return $this
     */
    public function convertLead()
    {
        $convert_lead = new ConvertLead($this->lead);

        $this->lead = $convert_lead->run();

        return $this;
    }
}
