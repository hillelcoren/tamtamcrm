<?php

namespace App\Services\Payment;

use App\Helpers\Email\PaymentEmail;
use App\Jobs\Payment\EmailPayment;
use App\Jobs\Quote\EmailQuote;
use App\Payment;
use App\Quote;
use App\Services\AbstractService;

class SendEmail extends AbstractService
{

    private $payment;
    private $contact;

    public function __construct(Payment $payment, $contact = null)
    {
        $this->payment = $payment;
        $this->contact = $contact;
    }

    /**
     * Builds the correct template to send
     * @param string $reminder_template The template name ie reminder1
     * @return array
     */
    public function run()
    {
        $email_builder = (new PaymentEmail())->build($this->payment, $this->contact);

        $this->payment->customer->contacts->each(function ($contact) use ($email_builder) {
            if ($contact->send_email && $contact->email) {
                EmailPayment::dispatchNow($this->payment, $email_builder, $contact);
            }
        });
    }
}
