<?php

namespace App\Filters;

use App\Company;
use App\Repositories\CompanyRepository;
use App\Requests\SearchRequest;
use App\Transformations\CompanyTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyFilter extends QueryFilter
{
    use CompanyTransformable;

    private $companyRepository;

    private $model;

    /**
     * CompanyFilter constructor.
     * @param CompanyRepository $companyRepository
     */
    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->model = $companyRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'name' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('companies.*')
                                   ->leftJoin('company_contacts', 'company_contacts.company_id', '=', 'companies.id');

        if ($request->has('status')) {
            $this->status($request->status);
        }

        if ($request->has('search_term') && !empty($request->search_term)) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $companies = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->companyRepository->paginateArrayResults($companies, $recordsPerPage);
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
            $query->where('companies.name', 'like', '%' . $filter . '%')
                //->orWhere('companies.id_number', 'like', '%'.$filter.'%')
                  ->orWhere('company_contacts.first_name', 'like', '%' . $filter . '%')
                  ->orWhere('company_contacts.last_name', 'like', '%' . $filter . '%')
                  ->orWhere('company_contacts.email', 'like', '%' . $filter . '%')
                  ->orWhere('companies.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('companies.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('companies.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('companies.custom_value4', 'like', '%' . $filter . '%');
        });
    }

    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    private function addAccount(int $account_id)
    {
        $this->query->where('companies.account_id', '=', $account_id);
    }

    public function balance(string $balance): Builder
    {
        $parts = $this->split($balance);

        return $this->query->where('balance', $parts->operator, $parts->value);
    }

    /**
     * Filter between balances
     *
     * @param string balance
     * @return Illuminate\Database\Query\Builder
     */
    public function between_balance(string $balance): Builder
    {
        $parts = explode(":", $balance);

        return $this->query->whereBetween('balance', [$parts[0], $parts[1]]);
    }

    /**
     * @param $list
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $companies = $list->map(function (Company $company) {
            return $this->transformCompany($company);
        })->all();

        return $companies;
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
        $table = 'companies';
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

}
