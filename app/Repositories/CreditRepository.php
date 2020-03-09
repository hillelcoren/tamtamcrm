<?php

namespace App\Repositories;

use App\ClientContact;
use App\Factory\CreditInvitationFactory;
use App\CreditInvitation;
use App\Repositories\Base\BaseRepository;
use App\Credit;
use App\Payment;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\CreditRepositoryInterface;
use App\Libraries\Utils;
use App\Customer;
use Illuminate\Support\Collection;
use App\Traits\GeneratesCounter;

class CreditRepository extends BaseRepository implements CreditRepositoryInterface
{
    use GeneratesCounter;

    /**
     * PaymentRepository constructor.
     * @param Payment $payment
     */
    public function __construct(Credit $credit)
    {
        parent::__construct($credit);
        $this->model = $credit;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function save(array $data, Credit $credit): ?Credit
    {
        $credit->fill($data);

        $credit->save();

        if (isset($data['client_contacts'])) {
            foreach ($data['client_contacts'] as $contact) {
                if ($contact['send_email'] == 1 && is_string($contact['id'])) {
                    $client_contact = ClientContact::find($contact['id']);
                    $client_contact->send_email = true;
                    $client_contact->save();
                }
            }
        }

        if (isset($data['invitations'])) {
            $invitations = collect($data['invitations']);

            /* Get array of Keys which have been removed from the invitations array and soft delete each invitation */
            $credit->invitations->pluck('key')->diff($invitations->pluck('key'))->each(function ($invitation) {

                $invite = $this->getInvitationByKey($invitation);

                if ($invite) {
                    $invite->forceDelete();
                }

            });

            foreach ($data['invitations'] as $invitation) {
                $inv = false;

                if (array_key_exists('key', $invitation)) {
                    $inv = $this->getInvitationByKey($invitation['key']);
                }

                if (!$inv) {

                    if (isset($invitation['id'])) {
                        unset($invitation['id']);
                    }

                    //make sure we are creating an invite for a contact who belongs to the client only!
                    $contact = ClientContact::find($invitation['client_contact_id']);

                    if ($credit->customer_id == $contact->customer_id) {
                        $new_invitation = CreditInvitationFactory::create($credit->account_id, $credit->user_id);
                        $new_invitation->fill($invitation);
                        $new_invitation->credit_id = $credit->id;
                        $new_invitation->client_contact_id = $invitation['client_contact_id'];
                        $new_invitation->save();
                    }
                }
            }
        }

        $credit->load('invitations');

        /* If no invitations have been created, this is our fail safe to maintain state*/
        if ($credit->invitations->count() == 0) {
            $credit->service()->createInvitations();
        }

        /**
         * Perform calculations on the
         * credit note
         */

        $credit = $credit->calc()->getCredit();
        $credit->save();

        if (!$credit->number) {
            $credit = $credit->service()->applyNumber()->save();
        }

        return $credit->fresh();
    }

    public function getCreditForCustomer(Customer $objCustomer)
    {
        return $this->model->where('customer_id', $objCustomer->id)->get();
    }

    public function getInvitationByKey($key): ?CreditInvitation
    {
        return CreditInvitation::whereRaw("BINARY `key`= ?", [$key])->first();
    }

    /**
     * @param int $id
     * @return Credit
     */
    public function findCreditById(int $id): Credit
    {
        return $this->findOneOrFail($id);
    }

    /**
     * List all the categories
     *
     * @param string $order
     * @param string $sort
     * @param array $except
     * @return Collection
     */
    public function listCredits(string $order = 'id', string $sort = 'desc', $except = []): Collection
    {
        return $this->model->orderBy($order, $sort)->get()->except($except);
    }
}
