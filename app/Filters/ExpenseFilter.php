<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Filters;

use App\Company;
use App\Expense;
use App\Repositories\CompanyRepository;
use App\Repositories\ExpenseRepository;
use App\Requests\SearchRequest;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Transformations\ExpenseTransformable;

/**
 * ExpenseFilters
 */
class ExpenseFilter extends QueryFilter
{
    use ExpenseTransformable;

    private $expense_repo;

    /**
     * CompanyFilter constructor.
     * @param CompanyRepository $companyRepository
     */
    public function __construct(ExpenseRepository $expense_repo)
    {
        $this->expense_repo = $expense_repo;
        $this->model = $expense_repo->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'amount' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->has('status')) {
            $this->status($request->status);
        }

        if ($request->has('search_term') && !empty($request->search_term)) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->filled('company_id')) {
            $this->query->whereCompanyId($request->company_id);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $expenses = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->expense_repo->paginateArrayResults($expenses, $recordsPerPage);
            return $paginatedResults;
        }

        return $expenses;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('start_date')));
        $end = date("Y-m-d", strtotime($request->input('end_date')));
        $this->query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * @param $list
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $expenses = $list->map(function (Expense $expense) {
            return $this->transformExpense($expense);
        })->all();

        return $expenses;
    }

    /**
     * Filter based on search text
     *
     * @param string query filter
     * @return Illuminate\Database\Query\Builder
     * @deprecated
     *
     */
    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return false;
        }

        return $this->query->where(function ($query) use ($filter) {
            $query->where('expenses.name', 'like', '%' . $filter . '%')
                  ->orWhere('expenses.id_number', 'like', '%' . $filter . '%')
                //->orWhere('expense_contacts.first_name', 'like', '%'.$filter.'%')
                //->orWhere('expense_contacts.last_name', 'like', '%'.$filter.'%')
                //->orWhere('expense_contacts.email', 'like', '%'.$filter.'%')
                  ->orWhere('expenses.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('expenses.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('expenses.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('expenses.custom_value4', 'like', '%' . $filter . '%');
        });
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
        $table = 'expenses';
        $filters = explode(',', $filter);

        $this->query->whereNull($table . '.id');
        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }

        if (in_array(parent::STATUS_ARCHIVED, $filters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
            });

            $this->query->withTrashed();
        }
        if (in_array(parent::STATUS_DELETED, $filters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }
    }

    /**
     * Sorts the list based on $sort
     *
     * @param string sort formatted as column|asc
     * @return Illuminate\Database\Query\Builder
     */
    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    /**
     * Filters the query by the users account ID
     *
     * @param $company_id The company Id
     * @return Illuminate\Database\Query\Builder
     */
    private function addAccount(int $account_id)
    {
        $this->query->where('account_id', '=', $account_id);
    }
}
