<?php

namespace App\Services\Task;

use App\Helpers\Email\OrderEmail;
use App\Jobs\Order\EmailOrder;

class SendEmail
{

    public $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Builds the correct template to send
     * @param string $reminder_template The template name ie reminder1
     * @return array
     */
    public function run()
    {
        $customer = $this->task->customer;
        $email_builder = (new OrderEmail())->build($this->task, $customer);

        if ($customer->send_invoice && $customer->email) {
            EmailOrder::dispatchNow($this->task, $email_builder, $customer);
        }
    }
}
