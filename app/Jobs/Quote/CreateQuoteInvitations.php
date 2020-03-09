<?php

namespace App\Jobs\Quote;

use App\Factory\QuoteInvitationFactory;
use App\Libraries\MultiDB;
use App\Quote;
use App\Account;
use App\QuoteInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class CreateQuoteInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $quote;
    private $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Quote $quote, Account $account)
    {
        $this->quote = $quote;
        $this->account = $account;
    }

    public function handle()
    {
        $contacts = $this->quote->customer->contacts;

        $contacts->each(function ($contact) {
            $invitation = QuoteInvitation::whereAccountId($this->quote->account_id)->whereClientContactId($contact->id)
                                         ->whereQuoteId($this->quote->id)->first();

            if (!$invitation && $contact->send) {
                $ii = QuoteInvitationFactory::create($this->quote->account_id, $this->quote->user_id);
                $ii->quote_id = $this->quote->id;
                $ii->client_contact_id = $contact->id;
                $ii->save();
            } elseif ($invitation && !$contact->send) {
                $invitation->delete();
            }
        });
    }
}
