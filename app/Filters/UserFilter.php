<?php

namespace App\Filters;

use App\Repositories\UserRepository;
use App\Requests\SearchRequest;
use App\Transformations\UserTransformable;
use App\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserFilter extends QueryFilter
{
    use UserTransformable;

    private $userRepository;

    private $model;

    /**
     * UserFilter constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->model = $userRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column || $request->column === 'name' ? 'first_name' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query =
            $this->model->select('users.*')->leftJoin('department_user', 'users.id', '=', 'department_user.user_id')
                        ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id');

        if ($request->has('status')) {
            $this->status($request->status);
        }

        if ($request->filled('role_id')) {
            $this->query->where('role_id', '=', $request->role_id);
        }

        if ($request->filled('department_id')) {
            $this->query->where('department_user.department_id', '=', $request->department_id);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->orderBy($orderBy, $orderDir);

        $this->query->whereHas('account_users', function ($query) use ($account_id) {
            $query->where('account_id', '=', $account_id);
        });

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        $this->query->groupBy('users.id');

        $users = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->userRepository->paginateArrayResults($users, $recordsPerPage);
            return $paginatedResults;
        }

        return $users;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('from_date')));
        $end = date("Y-m-d", strtotime($request->input('to_date') . "+1 day"));
        $this->query->whereBetween('created_at', [$start, $end]);
    }

    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('users.first_name', 'like', '%' . $filter . '%')
                  ->orWhere('users.last_name', 'like', '%' . $filter . '%')
                  ->orWhere('users.email', 'like', '%' . $filter . '%');
        });
    }

    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    /**
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $users = $list->map(function (User $user) {
            return $this->transformUser($user);
        })->all();

        return $users;
    }

    /**
     * Filters the list based on the status
     * archived, active, deleted
     * @param $query
     * @param string $filter
     * @return mixed
     */
    public function status(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }

        $table = 'users';
        $filters = explode(',', $filter);

        $this->query->whereNull($table . '.id');

        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }
        if (in_array(parent::STATUS_ARCHIVED, $filters)) {
            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
//                if (!in_array($table, ['users'])) {
//                    $query->where($table . '.is_deleted', '=', 0);
//                }
            });

            $this->query->withTrashed();
        }
        if (in_array(parent::STATUS_DELETED, $filters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }
    }
}
