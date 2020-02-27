<?php

namespace App\Factory;

use App\Lead;

class LeadFactory
{
    public static function create(int $account_id, int $user_id): Lead
    {
        $client = new Lead();
        $client->account_id = $account_id;
        $client->user_id = $user_id;
        $client->first_name = '';
        $client->last_name = '';
        $client->title = '';
        $client->description = '';
        $client->phone = '';
        $client->email = '';
        $client->private_notes = '';
        $client->public_notes = '';
        $client->website = '';
        $client->valued_at = 0;
        $client->source_type = 1;

        return $client;
    }
}
