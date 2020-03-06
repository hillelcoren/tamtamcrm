<?php

namespace App\Jobs\Order;

use App\Events\Invoice\InvoiceWasEmailed;
use App\Events\Invoice\InvoiceWasEmailedAndFailed;
use App\Helpers\Email\InvoiceEmail;
use App\InvoiceInvitation;
use App\Jobs\Utils\SystemLogger;
use App\Mail\TemplateEmail;
use App\Invoice;
use App\Account;
use App\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Test\Constraint\EmailTextBodyContains;

class EmailOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customer;

    private $email_builder;

    private $task;

    /**
     * EmailQuote constructor.
     * @param BuildEmail $email_builder
     * @param QuoteInvitation $quote_invitation
     */
    public function __construct($task, $email_builder, $customer)
    {
        $this->customer = $customer;
        $this->email_builder = $email_builder;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     *
     * @return void
     */
    public function handle()
    {
        $email_builder = $this->email_builder;

        Mail::to($this->customer->contacts->first()->email, $this->customer->present()->name())
            ->send(new TemplateEmail($email_builder, $this->customer->user, $this->customer));

        if (count(Mail::failures()) > 0) {
            return $this->logMailError($errors);
        }
    }

    private function logMailError($errors)
    {
        SystemLogger::dispatch($errors, SystemLog::CATEGORY_MAIL, SystemLog::EVENT_MAIL_SEND, SystemLog::TYPE_FAILURE,
            $this->task->customer);
    }
}
