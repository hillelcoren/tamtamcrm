<?php

namespace App\Filters;

use App\Quote;
use App\Repositories\QuoteRepository;
use App\Requests\SearchRequest;
use App\Transformations\QuoteTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class QuoteFilter extends QueryFilter
{
    use QuoteTransformable;

    private $quoteRepository;

    private $model;

    /**
     * QuoteFilter constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
        $this->model = $quoteRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'due_date' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->has('status')) {
            $this->status($request->status);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $quotes = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->quoteRepository->paginateArrayResults($quotes, $recordsPerPage);
            return $paginatedResults;
        }

        return $quotes;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('start_date')));
        $end = date("Y-m-d", strtotime($request->input('end_date')));
        $this->query->whereBetween('created_at', [$start, $end]);
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('quotes.number', 'like', '%' . $filter . '%')
                  ->orWhere('quotes.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('quotes.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('quotes.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('quotes.custom_value4', 'like', '%' . $filter . '%');
        });
    }

    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    private function addAccount(int $account_id)
    {
        $this->query->where('account_id', '=', $account_id);
    }

    /**
     * @param $list
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $quotes = $list->map(function (Quote $quote) {
            return $this->transformQuote($quote);
        })->all();

        return $quotes;
    }

    /**
     * Filters the list based on the status
     * archived, active, deleted
     *
     * @param string filter
     * @return Illuminate\Database\Query\Builder
     */
    public function status(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }

        $table = 'quotes';
        $filters = explode(',', $filter);

        $this->query->whereNull($table . '.id');
        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }

        if (in_array(parent::STATUS_ARCHIVED, $filters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
                //if (! in_array($table, ['users'])) {
                //$query->where($table . '.is_deleted', '=', 0);
                //}
            });

            $this->query->withTrashed();
        }
        if (in_array(parent::STATUS_DELETED, $filters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }
    }
}
