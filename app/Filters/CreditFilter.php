<?php

namespace App\Filters;

use App\Company;
use App\Credit;
use App\Repositories\CompanyRepository;
use App\Repositories\CreditRepository;
use App\Requests\SearchRequest;
use App\Transformations\CreditTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class CreditFilter extends QueryFilter
{
    use CreditTransformable;

    private $credit_repo;

    private $model;

    /**
     * CompanyFilter constructor.
     * @param CompanyRepository $companyRepository
     */
    public function __construct(CreditRepository $credit_repository)
    {
        $this->credit_repo = $credit_repository;
        $this->model = $credit_repository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'total' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->has('status_id')) {
            $this->status($request->status_id);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $companies = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->credit_repo->paginateArrayResults($companies, $recordsPerPage);
            return $paginatedResults;
        }

        return $companies;
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
            $query->where('credits.number', 'like', '%' . $filter . '%')
                  ->orWhere('credits.number', 'like', '%' . $filter . '%')
                  ->orWhere('credits.date', 'like', '%' . $filter . '%')
                  ->orWhere('credits.amount', 'like', '%' . $filter . '%')
                  ->orWhere('credits.balance', 'like', '%' . $filter . '%')
                  ->orWhere('credits.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('credits.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('credits.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('credits.custom_value4', 'like', '%' . $filter . '%');
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
        $credits = $list->map(function (Credit $credit) {
            return $this->transformCredit($credit);
        })->all();

        return $credits;
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

        $table = 'credits';
        $status_parameters = explode(',', $filter);

        $this->query->whereNull($table . '.id');

        if (in_array(parent::STATUS_ACTIVE, $status_parameters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }

        if (in_array(parent::STATUS_ARCHIVED, $status_parameters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
            });

            $this->query->withTrashed();
        }

        if (in_array(parent::STATUS_DELETED, $status_parameters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }

        if (in_array('draft', $status_parameters)) {
            $this->query->where('status_id', Credit::STATUS_DRAFT);
        }

        if (in_array('partial', $status_parameters)) {
            $this->query->where('status_id', Credit::STAUTS_PARTIAL);
        }

        if (in_array('applied', $status_parameters)) {
            $this->query->where('status_id', Credit::STATUS_APPLIED);
        }
    }

}
