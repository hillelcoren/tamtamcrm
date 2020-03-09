<?php

namespace App\Filters;

use App\Event;
use App\Repositories\EventRepository;
use App\Requests\SearchRequest;
use App\Transformations\EventTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class EventFilter extends QueryFilter
{
    use EventTransformable;

    private $eventRepository;

    private $model;

    /**
     * CompanyFilter constructor.
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->model = $eventRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'title' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->has('search_term') && !empty($request->search_term)) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->has('status_id')) {
            $this->status($request->status_id);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $events = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->eventRepository->paginateArrayResults($events, $recordsPerPage);
            return $paginatedResults;
        }

        return $events;
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('location', 'like', '%' . $filter . '%')->orWhere('title', 'like', '%' . $filter . '%')
                  ->orWhere('description', 'like', '%' . $filter . '%');
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
        $events = $list->map(function (Event $event) {
            return $this->transformEvent($event);
        })->all();

        return $events;
    }

    /**
     * @param $filters
     * @param int $account_id
     * @return mixed
     */
    public function filterBySearchCriteria($filters, int $account_id)
    {
        $this->query = $this->model->select('events.*')->join('event_user', 'event_user.user_id', '=', 'events.id');
        foreach ($filters as $column => $value) {

            if (empty($value)) {
                continue;
            }

            if ($column === 'status_id') {
                $this->status($value);
                continue;
            }
            $this->query->where($column, '=', $value);
        }

        $this->addAccount($account_id);

        return $this->transformList();
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
        $table = 'events';
        $filters = explode(',', $filter);

        $this->query->whereNull($table . '.id');
        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }

        if (in_array(parent::STATUS_ARCHIVED, $filters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
                //if (!in_array($table, ['users'])) {
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
