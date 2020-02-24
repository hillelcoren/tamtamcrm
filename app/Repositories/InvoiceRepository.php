<?php

namespace App\Repositories;

use App\Customer;
use App\Factory\InvoiceInvitationFactory;
use App\Invoice;
use App\ClientContact;
use App\InvoiceInvitation;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use Exception;
use Illuminate\Support\Collection;
use App\Task;
use App\Helpers\Invoice\InvoiceSum;
use App\Jobs\Product\UpdateOrCreateProduct;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{

    /**
     * InvoiceRepository constructor.
     * @param Order $invoice
     */
    public function __construct(Invoice $invoice)
    {
        parent::__construct($invoice);
        $this->model = $invoice;
    }

    /**
     * Delete a customer
     *
     * @return bool
     * @throws Exception
     */
    public function deleteInvoice(): bool
    {
        return $this->delete();
    }

    /**
     * @param int $id
     *
     * @return Invoice
     * @throws Exception
     */
    public function findInvoiceById(int $id): Invoice
    {
        return $this->findOneOrFail($id);
    }

    public function getInvitationByKey($key)
    {
        return InvoiceInvitation::whereRaw("BINARY `key`= ?", [$key])->first();
    }

    /**
     * List all the invoices
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listInvoices(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection
    {
        return $this->all($columns, $order, $sort);
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Task $objTask
     * @return Collection
     */
    public function getInvoiceForTask(Task $objTask): Invoice
    {

        return $this->model->where('task_id', '=', $objTask->id)->first();
    }

    public function findInvoicesByStatus(int $status): Collection
    {
        return $this->model->where('status_id', '=', $status)->get();
    }

    /**
     * Saves the invoices
     *
     * @param array .                                        $data     The invoice data
     * @param InvoiceSum|\App\Models\Invoice $invoice The invoice
     *
     * @return     Invoice|InvoiceSum|\App\Models\Invoice|null  Returns the invoice object
     */
    public function save($data, Invoice $invoice): ?Invoice
    {
        /* Always carry forward the initial invoice amount this is important for tracking client balance changes later......*/
        $starting_amount = $invoice->total;
        $invoice->fill($data);

        $invoice->save();
        if (isset($data['client_contacts'])) {
            foreach ($data['client_contacts'] as $contact) {
                if ($contact['send_email'] == 1) {
                    $client_contact = ClientContact::find($contact['id']);
                    $client_contact->send_email = true;
                    $client_contact->save();
                }
            }
        }


        if (isset($data['invitations'])) {
            $invitations = collect($data['invitations']);

            /* Get array of Keys which have been removed from the invitations array and soft delete each invitation */
            $invoice->invitations->pluck('key')->diff($invitations->pluck('key'))->each(function ($invitation) {

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

                    $new_invitation = InvoiceInvitationFactory::create($invoice->account_id, $invoice->user_id);
                    //$new_invitation->fill($invitation);
                    $new_invitation->invoice_id = $invoice->id;
                    $new_invitation->client_contact_id = $invitation['client_contact_id'];
                    $new_invitation->save();

                }
            }
        }

        /* If no invitations have been created, this is our fail safe to maintain state*/
        if ($invoice->invitations->count() == 0) {
            $invoice->service()->createInvitations();
        }
        $invoice = $invoice->calc()->getInvoice();

//$invoice_calc = new InvoiceSum($invoice, $invoice->settings);
//$invoice = $invoice_calc->build()->getInvoice();
        $invoice->save();
        $finished_amount = $invoice->total;

        /**/
        if ($finished_amount != $starting_amount) {
            $invoice->ledger()->updateInvoiceBalance(($finished_amount - $starting_amount));
        }

        $invoice = $invoice->service()->applyNumber()->save();

        if (!empty($invoice->line_items) && $invoice->account->update_products !== false) {

            UpdateOrCreateProduct::dispatch($invoice->line_items, $invoice);
        }

        return $invoice->fresh();
    }


    /**
     * Mark the invoice as sent.
     *
     * @param \App\Models\Invoice $invoice The invoice
     *
     * @return     Invoice|\App\Models\Invoice|null  Return the invoice object
     */
    public function markSent(Invoice $invoice): ?Invoice
    {
        return $invoice->service()->markSent()->save();
    }

}
