<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 14/02/2020
 * Time: 19:51
 */

namespace App\Helpers\Email;


use App\Quote;
use App\QuoteInvitation;

class QuoteEmail extends EmailBuilder
{

    public function build(QuoteInvitation $invitation, $reminder_template, $contact = null)
    {
        $client = $invitation->customer;
        $quote = $invitation->quote;
        $contact = $invitation->contact;

        $this->template_style = $quote->customer->getSetting('email_style');

        $body_template = $client->getSetting('email_template_' . $reminder_template);

        /* Use default translations if a custom message has not been set*/
        if (iconv_strlen($body_template) == 0) {
            $body_template = trans('texts.quote_message', [
                'amount' => $quote->amount,
                'account' => $quote->account->present()->name()
            ], null, $quote->customer->locale());
        }

        $subject_template = $client->getSetting('email_subject_' . $reminder_template);

        if (iconv_strlen($subject_template) == 0) {
            if ($reminder_template == 'quote') {
                $subject_template = trans('texts.quote_subject', [
                    'number' => $quote->number,
                    'account' => $quote->account->present()->name()
                ], null, $quote->customer->locale());
            } else {
                $subject_template = trans('texts.reminder_subject', [
                    'number' => $quote->number,
                    'account' => $quote->account->present()->name()
                ], null, $quote->customer->locale());
            }
        }

        $this->setTemplate($quote->customer->getSetting('email_style'))->setContact($contact)
             ->setFooter("<a href='{$invitation->getLink()}'>Quote Link</a>")
             ->setVariables($quote->makeValues($contact))->setSubject($subject_template)->setBody($body_template);

        if ($client->getSetting('pdf_email_attachment') !== false) {
            $this->attachments = $quote->pdf_file_path();
        }

        return $this;
    }
}
