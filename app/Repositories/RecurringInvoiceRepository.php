<?php

namespace App\Repositories;

use App\Repositories\Base\BaseRepository;
use App\Helpers\Invoice\InvoiceSum;
use App\RecurringInvoice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\GeneratesCounter;

/**
 * RecurringInvoiceRepository
 */
class RecurringInvoiceRepository extends BaseRepository
{
    use GeneratesCounter;

    /**
     * RecurringInvoiceRepository constructor.
     * @param RecurringInvoice $invoice
     */
    public function __construct(RecurringInvoice $invoice)
    {
        parent::__construct($invoice);
        $this->model = $invoice;
    }

    public function save($data, RecurringInvoice $invoice): ?RecurringInvoice
    {
        $invoice->fill($data);

        $invoice->save();
        $invoice_calc = new InvoiceSum($invoice);
        $invoice = $invoice_calc->build()->getInvoice();

        if (!$invoice->number) {
            $invoice->number = $this->getNextRecurringInvoiceNumber($invoice->customer);
        }

        $invoice->save();
//fire events here that cascading from the saving of an invoice
//ie. client balance update...

        return $invoice;
    }

    /**
     * List all the invoices
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function listInvoices(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Find the product by ID
     *
     * @param int $id
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findInvoiceById(int $id): RecurringInvoice
    {
        return $this->findOneOrFail($id);
    }

    public function getModel()
    {
        return $this->model;
    }
}
