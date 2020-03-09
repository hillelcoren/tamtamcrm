<?php

namespace App\Jobs\Task;

//use App\Events\Account\AccountCreated;
//use App\Jobs\Company\CreateCompany;
//use App\Jobs\Company\CreateCompanyToken;
//use App\Jobs\User\CreateUser;
use App\Task;
use App\User;

//use App\Notifications\NewAccountCreated;
//use App\Utils\Traits\UserSessionAttributes;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SaveTaskTimes
{
    use Dispatchable;
    protected $request;

    private $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request, Task $task)
    {
        $this->request = $request;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): ?Task
    {
        if (isset($this->data['time_log'])) {
            $timeLog = json_decode($this->data['time_log']);
        } elseif ($this->task->time_log) {
            $timeLog = json_decode($this->task->time_log);
        } else {
            $timeLog = [];
        }
        array_multisort($timeLog);
        if (isset($this->data['action'])) {
            if ($this->data['action'] == 'start') {
                $this->task->is_running = true;
                $timeLog[] = [strtotime('now'), false];
            } elseif ($this->data['action'] == 'resume') {
                $this->task->is_running = true;
                $timeLog[] = [strtotime('now'), false];
            } elseif ($this->data['action'] == 'stop' && $this->task->is_running) {
                $timeLog[count($timeLog) - 1][1] = time();
                $this->task->is_running = false;
            } elseif ($this->data['action'] == 'offline') {
                $this->task->is_running = $this->data['is_running'] ? 1 : 0;
            }
        } elseif (isset($this->data['is_running'])) {
            $this->task->is_running = $this->data['is_running'] ? 1 : 0;
        }
        $this->task->time_log = json_encode($timeLog);
        $this->task->save();

        return $this->task;
    }
}
