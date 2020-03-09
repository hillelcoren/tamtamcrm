<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 14/02/2020
 * Time: 19:51
 */

namespace App\Helpers\Email;


use App\Payment;

class PaymentEmail extends EmailBuilder
{
    public function build(Payment $payment, $contact = null)
    {
        $client = $payment->customer;

        $body_template = $client->getSetting('email_template_payment');

        /* Use default translations if a custom message has not been set*/
        if (iconv_strlen($body_template) == 0) {

            $body_template = trans('texts.payment_message', [
                'amount' => $payment->amount,
                'account' => $payment->account->present()->name()
            ], null, $this->customer->locale());
        }

        $subject_template = $client->getSetting('email_subject_payment');

        if (iconv_strlen($subject_template) == 0) {
            $subject_template = trans('texts.payment_subject', [
                'number' => $payment->number,
                'account' => $payment->account->present()->name()
            ], null, $payment->customer->locale());
        }

        $this->setTemplate($payment->customer->getSetting('email_style'))->setSubject($subject_template)
             ->setBody($body_template);

        return $this;
    }
}
