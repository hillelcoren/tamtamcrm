<?php

namespace App\Filters;

use App\Invoice;
use App\RecurringQuote;
use App\Repositories\RecurringQuoteRepository;
use App\Requests\SearchRequest;
use App\Transformations\RecurringQuoteTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class RecurringQuoteFilter extends QueryFilter
{
    use RecurringQuoteTransformable;

    private $recurringQuoteRepository;

    private $model;

    /**
     * RecurringQuoteFilter constructor.
     * @param RecurringQuoteRepository $recurringQuoteRepository
     */
    public function __construct(RecurringQuoteRepository $recurringQuoteRepository)
    {
        $this->recurringQuoteRepository = $recurringQuoteRepository;
        $this->model = $recurringQuoteRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'due_date' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->has('status')) {
            $this->filterStatus($request->status);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $quotes = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->recurringQuoteRepository->paginateArrayResults($quotes, $recordsPerPage);
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

    private function transformList()
    {
        $list = $this->query->get();

        $quotes = $list->map(function (RecurringQuote $quote) {
            return $this->transformQuote($quote);
        })->all();

        return $quotes;
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('recurring_quotes.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('recurring_quotes.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('recurring_quotes.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('recurring_quotes.custom_value4', 'like', '%' . $filter . '%');
        });
    }

    /**
     * @param $orderBy
     * @param $orderDir
     */
    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    /**
     * @param int $account_id
     */
    private function addAccount(int $account_id)
    {
        $this->query->where('account_id', '=', $account_id);
    }

    private function filterStatus($filter)
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        $status_parameters = explode(',', $filter);

        if (in_array('all', $status_parameters)) {
            return $this->query;
        }

        if (in_array('paid', $status_parameters)) {
            $this->query->where('status_id', Invoice::STATUS_PAID);
        }
        if (in_array('unpaid', $status_parameters)) {
            $this->query->whereIn('status_id', [Invoice::STATUS_SENT, Invoice::STATUS_PARTIAL]);
        }
        if (in_array('overdue', $status_parameters)) {
            $this->query->whereIn('status_id', [
                Invoice::STATUS_SENT,
                Invoice::STATUS_PARTIAL
            ])->where('due_date', '<', Carbon::now())->orWhere('partial_due_date', '<', Carbon::now());
        }

        $table = 'recurring_quotes';

        if (in_array(parent::STATUS_ARCHIVED, $status_parameters)) {

            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
                if (!in_array($table, ['users'])) {
                    $query->where($table . '.is_deleted', '=', 0);
                }
            });

            $this->query->withTrashed();
        }

        if (in_array(parent::STATUS_DELETED, $status_parameters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }

    }
}
