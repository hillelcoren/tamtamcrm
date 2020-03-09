<?php

namespace App\Services\Task;

use App\Factory\CustomerFactory;
use App\Factory\TaskFactory;
use App\Models\Invoice;
use App\Repositories\ClientContactRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\InvoiceSum;
use App\Repositories\TaskRepository;
use App\Services\Task\ConvertLead;
use Illuminate\Http\Request;
use App\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Transformations\TaskTransformable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskCreated;
use App\Services\EntityManager;

/**
 * Class TaskService
 * @package App\Services\Task
 */
class TaskService
{
    protected $task;

    /**
     * TaskService constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @param Request $request
     * @param CustomerRepository $customer_repo
     * @param TaskRepository $task_repo
     * @param bool $is_deal
     * @return Invoice|InvoiceSum|Task|null
     */
    public function sendEmail()
    {
        $send_email = new SendEmail($this->task);

        $this->task = $send_email->run();

        return $this->task;
    }

    /**
     * @param Request $request
     * @param CustomerRepository $customer_repo
     * @param TaskRepository $task_repo
     * @param bool $is_deal
     * @return Invoice|InvoiceSum|Task|null
     */
    public function createDeal(Request $request,
        CustomerRepository $customer_repo,
        TaskRepository $task_repo,
        $is_deal = true)
    {
        $create_deal = new CreateDeal($this->task, $request, $customer_repo, $task_repo, $is_deal);

        $this->task = $create_deal->run();

        return $this->task;
    }

    /**
     * @param Request $request
     * @param CustomerRepository $customer_repo
     * @param TaskRepository $task_repo
     * @param bool $is_lead
     * @return mixed
     */
    public function updateLead(Request $request,
        CustomerRepository $customer_repo,
        TaskRepository $task_repo,
        $is_lead = true)
    {
        $update_lead = new UpdateLead($this->task, $request, $customer_repo, $task_repo, $is_lead);

        $this->task = $update_lead->run();
        return $this->task;
    }

    public function setTaskType($task_type)
    {
        $this->task->task_type = $task_type;

        return $this;
    }

    /**
     * @return Task|null
     */
    public function save(): ?Task
    {
        $this->task->save();
        return $this->task;
    }
}
