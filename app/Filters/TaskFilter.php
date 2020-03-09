<?php

namespace App\Filters;

use App\Task;
use App\Repositories\TaskRepository;
use App\Requests\SearchRequest;
use App\Transformations\TaskTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskFilter extends QueryFilter
{
    use TaskTransformable;

    private $taskRepository;

    private $model;

    /**
     * TaskFilter constructor.
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->model = $taskRepository->getModel();
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

        $this->query =
            $this->model->select('*', 'tasks.id as id')->leftJoin('task_user', 'tasks.id', '=', 'task_user.task_id');

        if ($request->has('search_term') && !empty($request->search_term)) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->filled('project_id')) {
            $this->query->whereProjectId($request->project_id);
        }

        if ($request->filled('task_status')) {
            $this->status($request->task_status);
        }

        if ($request->filled('task_type')) {
            $this->query->whereTaskType($request->task_type);
        }

        if ($request->filled('user_id')) {
            $this->query->where('task_user.user_id', '=', $request->user_id);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $tasks = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->taskRepository->paginateArrayResults($tasks, $recordsPerPage);
            return $paginatedResults;
        }

        return $tasks;
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('title', 'like', '%' . $filter . '%')->orWhere('content', 'like', '%' . $filter . '%')
                  ->orWhere('rating', 'like', '%' . $filter . '%')
                  ->orWhere('custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('custom_value4', 'like', '%' . $filter . '%');
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
        $tasks = $list->map(function (Task $task) {
            return $this->transformTask($task);
        })->all();

        return $tasks;
    }

    /**
     * @param $filters
     * @param int $account_id
     * @return mixed
     */
    public function filterBySearchCriteria($filters, int $task_type, int $account_id)
    {
        $this->query = $this->model->select('tasks.id as id', 'tasks.*')
                                   ->leftJoin('task_user', 'tasks.id', '=', 'task_user.task_id');
        $this->query = $this->query->where('is_completed', 0)->where('task_type', $task_type)->where('parent_id', 0);

        foreach ($filters as $column => $value) {

            if (empty($value)) {
                continue;
            }

            if ($column === 'task_status' && $value === parent::STATUS_ARCHIVED) {
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
        $table = 'tasks';
        $filters = explode(',', $filter);

        $this->query->whereNull($table . '.id');
        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
            return true;
        }

        if (in_array(parent::STATUS_ARCHIVED, $filters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
                //if (!in_array($table, ['users'])) {
                //$query->where($table . '.is_deleted', '=', 0);
                //}
            });

            $this->query->withTrashed();
            return true;
        }

        if (in_array(parent::STATUS_DELETED, $filters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
            return true;
        }

        $this->query->orWhere($table . '.task_status', '=', 1);
        return true;
    }

}
