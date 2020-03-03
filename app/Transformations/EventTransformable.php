<?php

namespace App\Transformations;

use App\Event;
use App\Customer;
use App\Repositories\CustomerRepository;

trait EventTransformable
{

    /**
     * Transform the event
     *
     * @param Event $event
     * @return Event
     */
    protected function transformEvent(Event $event)
    {
        $prop = new Event;

        $customer = $event->customer;

        $prop->id = (int)$event->id;
        $prop->location = $event->location;
        $prop->customer_id = $customer->id;
        $prop->name = $customer->name;
        $prop->title = $event->title;
        $prop->beginDate = date("D M d Y H:i:s", strtotime($event->beginDate));
        $prop->endDate = date("D M d Y H:i:s", strtotime($event->endDate));
        $prop->attendees = $event->users;
        $prop->event_type = $event->event_type;
        $prop->description = $event->description;
        $prop->status = isset($event->status) ? $event->status : null;
        $prop->owner = $event->createdBy;
        $prop->deleted_at = $event->deleted_at;

        return $prop;
    }

}
