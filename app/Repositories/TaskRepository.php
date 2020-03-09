<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Task;
use App\Project;
use App\Repositories\ProjectRepository;
use App\User;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use App\Exceptions\CreateTaskErrorException;
use Exception;
use Illuminate\Support\Collection as Support;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\sendEmailNotificationToAdminMailable;
use App\Mail\SendOrderToCustomerMailable;
use App\Product;
use Illuminate\Support\Facades\Mail;
use App\Events\OrderCreateEvent;
use App\Repositories\ProductRepository;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    private $project_repo;

    /**
     * TaskRepository constructor.
     *
     * @param Task $task
     */
    public function __construct(Task $task, ProjectRepository $project_repo)
    {
        parent::__construct($task);
        $this->model = $task;
        $this->project_repo = $project_repo;
    }

    /**
     * Send email to customer
     */
    public function sendEmailToCustomer()
    {
//        Mail::to($this->model->customer)
//            ->send(new SendOrderToCustomerMailable($this->findTaskById($this->model->id)));
    }

    /**
     * Send email notification to the admin
     */
    public function sendEmailNotificationToAdmin()
    {
        $userRepo = new UserRepository(new User);
        $user = $userRepo->findUserById(9874);
//        Mail::to($user)
//            ->send(new sendEmailNotificationToAdminMailable($this->findTaskById($this->model->id)));
    }

    /**
     * @param int $id
     *
     * @return Task
     * @throws Exception
     */
    public function findTaskById(int $id): Task
    {
        return $this->findOneOrFail($id);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteTask(): bool
    {
        $result = $this->delete();
        $this->model->products()->detach();
        return $result;
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     *
     * @return Collection
     */
    public function listTasks($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Support
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

    /**
     *
     * @param Project $objProject
     * @param User $objUser
     * @return type
     */
    public function getTasksForProject(Project $objProject, User $objUser = null): Support
    {

        $query = $this->model->select('tasks.*')->where('project_id', $objProject->id)->where('is_completed', 0)
                             ->where('parent_id', 0);

        if ($objUser !== null) {
            $query->join('task_user', 'tasks.id', '=', 'task_user.task_id')->where('task_user.user_id', $objUser->id);
        }


        return $query->get();
    }

    /**
     *
     * @param int $task_type
     * @param type $limit
     * @return Support
     */
    public function getLeads($limit = null, User $objUser = null, int $account_id): Support
    {
        $query = $this->model->where('task_type', 2)->where('is_completed', 0)->where('parent_id', 0)
                             ->where('account_id', $account_id)->orderBy('tasks.created_at', 'desc');


        if ($objUser !== null) {
            $query->join('task_user', 'tasks.id', '=', 'task_user.task_id')->where('task_user.user_id', $objUser->id);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getDeals($limit = null, User $objUser = null): Support
    {
        $query = $this->model->where('task_type', 3)->where('is_completed', 0)->where('parent_id', 0)
                             ->orderBy('tasks.created_at', 'desc');

        if ($objUser !== null) {
            $query->join('task_user', 'tasks.id', '=', 'task_user.task_id')->where('task_user.user_id', $objUser->id);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sync the users
     *
     * @param array $params
     */
    public function syncUsers(Task $task, array $params)
    {
        return $task->users()->sync($params);
    }

    public function getTasksWithProducts(): Support
    {

        return $this->model->join('product_task', 'product_task.task_id', '=', 'tasks.id')->select('tasks.*')
                           ->groupBy('tasks.id')->get();
    }

    /**
     *
     * @param Task $objTask
     * @return Support
     */
    public function getSubtasks(Task $objTask): Support
    {
        return $this->model->where('parent_id', $objTask->id)->get();
    }

    /**
     *
     * @return type
     */
    public function getSourceTypeCounts(int $task_type, $account_id): Support
    {
        return $this->model->join('source_type', 'source_type.id', '=', 'tasks.source_type')
                           ->select('source_type.name', DB::raw('count(*) as value'))->where('task_type', $task_type)
                           ->where('tasks.account_id', $account_id)->groupBy('source_type.name')->get();
    }

    /**
     *
     * @return type
     */
    public function getStatusCounts(int $task_type, int $account_id): Support
    {
        return $this->model->join('task_statuses', 'task_statuses.id', '=', 'tasks.task_status')
                           ->select('task_statuses.title AS name',
                               DB::raw('CEILING(count(*) * 100 / (select count(*) from tasks)) as value'))
                           ->where('tasks.task_type', $task_type)->where('tasks.account_id', $account_id)
                           ->groupBy('task_statuses.title')->get();
    }

    /**
     *
     * @param int $task_type
     * @param int $number_of_days
     * @return type
     */
    public function getRecentTasks(int $task_type, int $number_of_days, int $account_id)
    {

        $date = Carbon::today()->subDays($number_of_days);
        $result = $this->model->select(DB::raw('count(*) as total'))->where('created_at', '>=', $date)
                              ->where('task_type', $task_type)->where('account_id', $account_id)->get();

        return !empty($result[0]) ? $result[0]['total'] : 0;
    }

    /**
     *
     * @param int $task_type
     * @return type
     */
    public function getNewDeals(int $task_type, int $account_id)
    {

        $result = $this->model->select(DB::raw('count(*) as total'))->where('task_type', $task_type)
                              ->where('account_id', $account_id)->get();

        return !empty($result[0]) ? $result[0]['total'] : 0;
    }

    /**
     *
     * @param int $task_type
     * @return type
     */
    public function getTotalEarnt(int $task_type, int $account_id)
    {

        return $this->model->where('task_type', $task_type)->where('account_id', $account_id)->sum('valued_at');
    }

    private function saveProjectTask($data, Task $task)
    {
        $objProject = (new ProjectRepository(new Project))->findProjectById($data['project_id']);
        $data['customer_id'] = $objProject->customer_id;
        $task->fill($data);

        $task->save();
        $objProject->tasks()->attach($task);

        if (isset ($data['contributors']) && !empty($data['contributors'])) {
            $this->syncUsers($task, $data['contributors']);
        }

        return $task->fresh();
    }

    /**
     * Saves the invoices
     *
     * @param array .                                        $data     The invoice data
     * @param InvoiceSum|Invoice $invoice The invoice
     *
     * @return     Invoice|InvoiceSum|Invoice|null  Returns the invoice object
     */
    public function save($data, Task $task): ?Task
    {
        if (empty($data['customer_id']) && !empty($data['project_id'])) {
            $project = $this->project_repo->findProjectById($data['project_id']);

            $data['customer_id'] = $project->customer_id;
        }

        $data['source_type'] = empty($data['source_type']) ? 1 : $data['source_type'];

//        if (isset($data['task_type']) && $data['task_type'] == 1 && !empty($data['project_id']) ) {
//           return $this->saveProjectTask($data, $task);
//        }
        $task->fill($data);
        $task->save();

        if (isset ($data['contributors']) && !empty($data['contributors'])) {
            $this->syncUsers($task, $data['contributors']);
        }

        return $task->fresh();
    }

}
