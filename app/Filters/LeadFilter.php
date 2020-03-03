<?php

namespace App\Filters;

use App\Lead;
use App\Repositories\LeadRepository;
use App\Transformations\LeadTransformable;
use Illuminate\Http\Request;

class LeadFilter extends QueryFilter
{
    use LeadTransformable;
    private $lead_repo;
    private $model;

    /**
     * LeadFilter constructor.
     * @param LeadRepository $lead_repo
     */
    public function __construct(LeadRepository $lead_repo)
    {
        $this->lead_repo = $lead_repo;
        $this->model = $lead_repo->getModel();
    }

    public function filter(Request $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'title' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

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

        $leads = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->lead_repo->paginateArrayResults($leads, $recordsPerPage);
            return $paginatedResults;
        }

        return $leads;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('start_date')));
        $end = date("Y-m-d", strtotime($request->input('end_date')));
        $this->query->whereBetween('created_at', [$start, $end]);
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

        $table = 'leads';
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

//        echo '<pre>';
//        print_r($this->query->toSql());
//        die;
    }

    /**
     * @param $list
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $leads = $list->map(function (Lead $lead) {
            return $this->transformLead($lead);
        })->all();

        return $leads;
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }

        return $this->query->where(function ($query) use ($filter) {
            $query->where('leads.title', 'like', '%' . $filter . '%');
            //->orWhere('companies.id_number', 'like', '%'.$filter.'%')
//                ->orWhere('companies.custom_value1', 'like', '%' . $filter . '%')
//                ->orWhere('companies.custom_value2', 'like', '%' . $filter . '%')
//                ->orWhere('companies.custom_value3', 'like', '%' . $filter . '%')
//                ->orWhere('companies.custom_value4', 'like', '%' . $filter . '%');
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
}
