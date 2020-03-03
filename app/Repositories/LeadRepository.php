<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

use App\Lead;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Message;
use App\Customer;
use App\User;
use Exception;

/**
 * Description of MessageRepository
 *
 * @author michael.hampton
 */
class LeadRepository extends BaseRepository
{

    /**
     * MessageRepository constructor.
     * @param Message $message
     */
    public function __construct(Lead $lead)
    {
        parent::__construct($lead);
        $this->model = $lead;
    }

    /**
     * Create the message
     *
     * @param array $data
     *
     * @return Message
     */
    public function save(Lead $lead, array $data): Lead
    {
        $lead = $lead->fill($data);
        $lead->save();
        return $lead;
    }

    public function getLeads()
    {
        return $this->model->all();
    }

    /**
     * @param int $id
     * @return Lead
     */
    public function findLeadById(int $id): Lead
    {
        return $this->findOneOrFail($id);
    }

    public function getModel()
    {
        return $this->model;
    }

}
