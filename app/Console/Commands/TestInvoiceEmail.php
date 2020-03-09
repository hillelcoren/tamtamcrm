<?php

namespace App\Console\Commands;

use App\Customer;
use App\Invoice;
use App\InvoiceInvitation;
use App\Mail\TemplateEmail;
use App\Repositories\InvoiceRepository;
use App\Repositories\UserRepository;
use App\User;
use Parsedown;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestInvoiceEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-test-invoice-emails';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends Test Invoice Emails to check templates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Builds the correct template to send
     * @param App\Invoice $invoice The Invoice Model
     * @param string $reminder_template The template name ie reminder1
     * @return void
     */
    public function handle()
    {
        $invoice = (new InvoiceRepository(new Invoice))->findInvoiceById(789);
        $this->customer = $invoice->customer;
        $this->user = (new UserRepository(new User))->findUserById(9874);

        $reminder_template = null;
        $message_array = $this->getEmailData($invoice);
        $message_array['title'] = 'The title';
        $message_array['footer'] = 'The Footer';

        $template_style = $this->customer->getSetting('email_style');
        $template_style = 'light';

        $invitations = InvoiceInvitation::whereInvoiceId($invoice->id)->get();

        $invitations->each(function ($invitation) use ($message_array, $template_style) {
            $contact = Customer::find($invitation->customer_id)->first();

            if ($contact->send_invoice) {
                if ($contact->send_invoice && $contact->email) {
                    //there may be template variables left over for the specific contact? need to reparse here

                    //change the runtime config of the mail provider here:

                    //send message
                    Mail::to($contact->email)->send(new TemplateEmail($message_array, $template_style, $this->user,
                        $this->customer));
                    //fire any events

                    die('good');

                    //sleep(5);

                }
            }

        });
    }


    /**
     * Builds the correct template to send
     * @param App\Invoice $invoice The Invoice Model
     * @param string $reminder_template The template name ie reminder1
     * @return array
     */
    private function getEmailData(Invoice $invoice, $reminder_template = null): array
    {
        //client
        $customer = $invoice->customer;
        if (!$reminder_template) {
            $reminder_template = $this->calculateTemplate($invoice);
        }
        //Need to determine which email template we are producing
        $email_data = $this->generateTemplateData($invoice, $reminder_template);
        return $email_data;
    }


    /**
     * @param Invoice $invoice
     * @param string $reminder_template
     * @return array
     */
    private function generateTemplateData(Invoice $invoice, string $reminder_template): array
    {
        $data = [];
        $customer = $invoice->customer;
        $body_template = $customer->getSetting('email_template_' . $reminder_template);
        $subject_template = $customer->getSetting('email_subject_' . $reminder_template);
        $data['body'] = $this->parseTemplate($invoice, $body_template, false);
        $data['subject'] = $this->parseTemplate($invoice, $subject_template, true);
        return $data;
    }

    private function parseTemplate(Invoice $invoice, string $template_data, bool $is_markdown = true): string
    {
        $invoice_variables = $invoice->makeValues();
        //process variables
        $data = str_replace(array_keys($invoice_variables), array_values($invoice_variables), $template_data);
        //process markdown
        if ($is_markdown) {
            $data = Parsedown::instance()->line($data);
        }
        return $data;
    }

    private function calculateTemplate(Invoice $invoice): string
    {
        //if invoice is currently a draft, or being marked as sent, this will be the initial email
        $customer = $invoice->customer;
        //if the invoice
        if ($invoice->status_id == Invoice::STATUS_DRAFT || Carbon::parse($invoice->due_date) > now()) {
            return 'invoice';
        } else {
            if ($customer->getSetting('enable_reminder1') !== false &&
                $this->inReminderWindow($invoice, $customer->getSetting('schedule_reminder1'),
                    $customer->getSetting('num_days_reminder1'))) {
                return 'template1';
            } else {
                if ($customer->getSetting('enable_reminder2') !== false &&
                    $this->inReminderWindow($invoice, $customer->getSetting('schedule_reminder2'),
                        $customer->getSetting('num_days_reminder2'))) {
                    return 'template2';
                } else {
                    if ($customer->getSetting('enable_reminder3') !== false &&
                        $this->inReminderWindow($invoice, $customer->getSetting('schedule_reminder3'),
                            $customer->getSetting('num_days_reminder3'))) {
                        return 'template3';
                    }
                }
            }
        }
        //also implement endless reminders here
        //

    }

    private function inReminderWindow($invoice, $schedule_reminder, $num_days_reminder)
    {
        switch ($schedule_reminder) {
            case 'after_invoice_date':
                return Carbon::parse($invoice->date)->addDays($num_days_reminder)->startOfDay()->eq(Carbon::now()
                                                                                                          ->startOfDay());
                break;
            case 'before_due_date':
                return Carbon::parse($invoice->due_date)->subDays($num_days_reminder)->startOfDay()->eq(Carbon::now()
                                                                                                              ->startOfDay());
                break;
            case 'after_due_date':
                return Carbon::parse($invoice->due_date)->addDays($num_days_reminder)->startOfDay()->eq(Carbon::now()
                                                                                                              ->startOfDay());
                break;
            default:
                # code...
                break;
        }
    }
}
