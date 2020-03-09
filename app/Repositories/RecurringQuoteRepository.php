<?php

namespace App\Repositories;

use App\Helpers\Invoice\InvoiceSum;
use App\RecurringQuote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Repositories\Base\BaseRepository;
use App\Traits\GeneratesCounter;

/**
 * RecurringQuoteRepository
 */
class RecurringQuoteRepository extends BaseRepository
{
    use GeneratesCounter;

    /**
     * RecurringQuoteRepository constructor.
     * @param RecurringQuote $quote
     */
    public function __construct(RecurringQuote $quote)
    {
        parent::__construct($quote);
        $this->model = $quote;
    }

    public function save($data, RecurringQuote $quote): ?RecurringQuote
    {
        $quote->fill($data);

        $quote->save();
        $quote_calc = new InvoiceSum($quote);
        $quote = $quote_calc->build()->getQuote();

        if (!$quote->number) {
            $quote->number = $this->getNextRecurringQuoteNumber($quote->customer);
        }

        $quote->save();

//fire events here that cascading from the saving of an invoice
//ie. client balance update...

        return $quote;
    }

    /**
     * Find the product by ID
     *
     * @param int $id
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findQuoteById(int $id): RecurringQuote
    {
        return $this->findOneOrFail($id);
    }


    /**
     * List all the invoices
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function listQuotes(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection
    {
        return $this->all($columns, $order, $sort);
    }


    public function getModel()
    {
        return $this->model;
    }
}
