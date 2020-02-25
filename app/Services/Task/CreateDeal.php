<?php

namespace App\Services\Task;

use App\Factory\CustomerFactory;
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
        $this->request->customer_type = 2;
        $account_id = 1;
        $factory = (new CustomerFactory())->create($account_id, $this->task->user_id);

        $date = new DateTime(); // Y-m-d
        $date->add(new DateInterval('P30D'));
        $due_date = $date->format('Y-m-d');

        $customer =
            $this->customer_repo->save($this->request->except('_token', '_method', 'valued_at', 'title', 'description'),
                $factory);
        if ($this->request->has('address_1') && !empty($this->request->address_1)) {
            $customer->addresses()->create([
                'company_id' => $this->request->company_id,
                'job_title' => $this->request->job_title,
                'address_1' => $this->request->address_1,
                'address_2' => $this->request->address_2,
                'zip' => $this->request->zip,
                'city' => $this->request->city,
                'country_id' => 225,
                'status' => 1
            ]);
        }

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
