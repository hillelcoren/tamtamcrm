<?php

namespace App\Jobs\Credit;

use App\Jobs\Utils\SystemLogger;
use App\Mail\TemplateEmail;
use App\Account;
use App\Credit;
use App\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailCredit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $credit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
    }

    /**
     * Execute the job.
     *
     *
     * @return void
     */
    public function handle()
    {

        //todo - change runtime config of mail driver if necessary

        $template_style = $this->credit->customer->getSetting('email_style');

        $this->credit->invitations->each(function ($invitation) use ($template_style) {
            if ($invitation->contact->send_email && $invitation->contact->email) {
                $message_array = $this->credit->getEmailData('', $invitation->contact);
                $message_array['title'] = &$message_array['subject'];
                $message_array['footer'] = "Sent to " . $invitation->contact->present()->name();

                //change the runtime config of the mail provider here:

                //send message
                Mail::to($invitation->contact->email, $invitation->contact->present()->name())
                    ->send(new TemplateEmail($message_array, $template_style, $invitation->contact->user,
                        $invitation->contact->customer));

                if (count(Mail::failures()) > 0) {
                    //event(new CreditWasEmailedAndFailed($this->credit, Mail::failures()));

                    return $this->logMailError($errors);
                }

                //fire any events
                //event(new CreditWasEmailed($this->credit));

                //sleep(5);
            }
        });
    }

    private function logMailError($errors)
    {
        SystemLogger::dispatch($errors, SystemLog::CATEGORY_MAIL, SystemLog::EVENT_MAIL_SEND, SystemLog::TYPE_FAILURE,
            $this->credit->customer);
    }
}
