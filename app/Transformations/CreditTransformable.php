<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 02/12/2019
 * Time: 15:58
 */

namespace App\Transformations;

use App\Credit;
use App\CreditInvitation;


trait CreditTransformable
{

    /**
     * Transform the credit
     *
     * @param Credit $credit
     * @return Credit
     */
    protected function transformCredit(Credit $credit)
    {
        $prop = new Credit();
        $prop->number = $credit->number ?: '';
        $prop->id = (int)$credit->id;
        $prop->customer_id = (int)$credit->customer_id;
        $prop->invitations = $this->transformCreditInvitations($credit->invitations);
        $prop->deleted_at = $credit->deleted_at;
        $prop->user_id = $credit->user_id;
        $prop->total = (float)$credit->total;
        $prop->balance = (float)$credit->balance;
        $prop->status_id = (int)($credit->status_id ?: 1);
        $prop->design_id = (int)$credit->design_id;
        $prop->terms = $credit->terms ?: '';
        $prop->footer = $credit->footer ?: '';
        $prop->public_notes = $credit->public_notes ?: '';
        $prop->private_notes = $credit->private_notes ?: '';
        $prop->created_at = $credit->created_at;

        return $prop;
    }

    /**
     * @param $invitations
     * @return array
     */
    private function transformCreditInvitations($invitations)
    {
        if (empty($invitations)) {
            return [];
        }

        return $invitations->map(function (CreditInvitation $invitation) {
            return (new CreditInvitationTransformable())->transformCreditInvitation($invitation);
        })->all();
    }
}
