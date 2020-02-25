<?php

namespace App\Repositories;

use App\Jobs\Quote\ApplyQuoteNumber;
use App\ClientContact;
use App\Repositories\Base\BaseRepository;
use App\Quote;
use Exception;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\QuoteRepositoryInterface;
use Illuminate\Http\Request;
use App\Task;
use App\Helpers\Invoice\InvoiceSum;
use App\Factory\QuoteInvitationFactory;
use App\Jobs\Quote\CreateQuoteInvitations;
use App\QuoteInvitation;
use App\Customer;

class QuoteRepository extends BaseRepository implements QuoteRepositoryInterface
{

    /**
     * QuoteRepository constructor.
     *
     * @param Quote $quote
     */
    public function __construct(Quote $quote)
    {
        parent::__construct($quote);
        $this->model = $quote;
    }

    /**
     * Sync the tasks
     *
     * @param array $params
     */
    /*public function syncTasks(int $task_id)
    {
        $this->model->tasks()->sync($task_id);
    } */

    /**
     * @param int $id
     *
     * @return Quote
     * @throws Exception
     */
    public function findQuoteById(int $id): Quote
    {
        return $this->findOneOrFail($id);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function save($data, Quote $quote): ?Quote
    {
        $quote->fill($data);
        $quote->save();

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
            $quote->invitations->pluck('key')->diff($invitations->pluck('key'))->each(function ($invitation) {
                $this->getInvitationByKey($invitation)->delete();
            });

            foreach ($data['invitations'] as $invitation) {
                $inv = false;

                if (array_key_exists('key', $invitation)) {
                    $inv = $this->getInvitationByKey([$invitation['key']]);

                    if ($inv) {
                        $inv->forceDelete();
                    }

                }

                if (!$inv) {

                    if (isset($invitation['id'])) {
                        unset($invitation['id']);
                    }

                    $new_invitation = QuoteInvitationFactory::create($quote->account_id, $quote->user_id);
                    $new_invitation->quote_id = $quote->id;
                    $new_invitation->client_contact_id = $invitation['client_contact_id'];
                    $new_invitation->save();

                }
            }
        }

        $quote->load('invitations');

        /* If no invitations have been created, this is our fail safe to maintain state*/
        if ($quote->invitations->count() == 0) {
            $quote->service()->createInvitations();
        }

        $quote = $quote->calc()->getQuote();
        $quote->save();
        $finished_amount = $quote->total;
        //todo need answers on this

        $quote = $quote->service()->applyNumber()->save();
        return $quote->fresh();
    }

    public function getInvitationByKey($key): QuoteInvitation
    {
        return QuoteInvitation::whereRaw("BINARY `key`= ?", [$key])->first();
    }

    /**
     * List all the invoices
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listQuotes(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     *
     * @param int $customerId
     * @return type
     */
    public function getQuoteForTask(Task $objTask): Quote
    {
        return $this->model->where('task_id', $objTask->id)->first();
    }

}
