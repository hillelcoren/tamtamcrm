<?php

namespace App\Events\Misc;

use App\Models\Invoice;
use Illuminate\Queue\SerializesModels;

/**
 * Class InvitationWasViewed.
 */
class InvitationWasViewed
{
    use SerializesModels;

    /**
     * @var Invoice
     */
    public $invitation;

    public $entity;

    /**
     * Create a new event instance.
     *
     * @param Invoice $invoice
     */
    public function __construct($entity, $invitation)
    {
        $this->entity = $entity;
        $this->invitation = $invitation;
    }
}
