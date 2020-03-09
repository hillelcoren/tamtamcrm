<?php

namespace App\Services\Task;

use App\ClientContact;
use App\Factory\ClientContactFactory;
use App\Factory\CustomerFactory;
use App\Repositories\ClientContactRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\TaskRepository;
use App\Services\AbstractService;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;

/**
 * Class CreateDeal
 * @package App\Services\Task
 */
class CreateDeal extends AbstractService
{
    private $task;
    private $request;
    private $customer_repo;
    private $task_repo;
    private $is_deal;

    /**
     * CreateDeal constructor.
     * @param $task
     * @param Request $request
     * @param CustomerRepository $customer_repo
     * @param TaskRepository $task_repo
     * @param $is_deal
     */
    public function __construct($task,
        Request $request,
        CustomerRepository $customer_repo,
        TaskRepository $task_repo,
        $is_deal)
    {
        $this->task = $task;
        $this->request = $request;
        $this->customer_repo = $customer_repo;
        $this->task_repo = $task_repo;
        $this->is_deal = $is_deal;
    }

    public function run()
    {
        $account_id = 1;
        $factory = (new CustomerFactory())->create($account_id, $this->task->user_id);

        $date = new DateTime(); // Y-m-d
        $date->add(new DateInterval('P30D'));
        $due_date = $date->format('Y-m-d');

        $contacts [] = [
            'first_name' => $this->request->first_name,
            'last_name' => $this->request->last_name,
            'email' => $this->request->email,
            'phone' => $this->request->phone,
        ];

        $customer = $this->customer_repo->save([
            'name' => $this->request->first_name . ' ' . $this->request->last_name,
            'phone' => $this->request->phone,
            'website' => $this->request->website,
            'currency_id' => 1,
            'default_payment_method' => 1,
            'contacts' => $contacts
        ], $factory);

        $this->task = $this->task_repo->save([
            'due_date' => $due_date,
            'created_by' => $this->task->user_id,
            'source_type' => $this->request->source_type,
            'title' => $this->request->title,
            'description' => $this->request->description,
            'customer_id' => $customer->id,
            'valued_at' => $this->request->valued_at,
            'task_type' => $this->is_deal === true ? 3 : 2,
            'task_status' => $this->request->task_status
        ], $this->task);

        if ($this->request->has('contributors')) {
            $this->task->users()->sync($this->request->input('contributors'));
        }

        return $this->task;
    }
}
