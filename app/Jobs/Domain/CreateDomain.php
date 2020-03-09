<?php

namespace App\Jobs\Domain;

use App\Events\Domain\DomainCreated;
use App\Jobs\Company\CreateCompany;
//use App\Jobs\Account\CreateCompanyToken;
use App\Jobs\User\CreateUser;
use App\Domain;
use App\Notifications\Ninja\NewAccountCreated;
use App\User;
use App\Notifications\NewDomainCreated;
use App\Utils\Traits\UserSessionAttributes;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CreateDomain
{
    use Dispatchable;
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): ?Domain
    {
        /*
         * Create domain
         */
        $domain = Domain::create($this->request);
        /*
         * Create account
         */
        $account = CreateCompany::dispatchNow($this->request, $domain);
        //$account->load('domain');
        /*
         * Set default company
         */
        $domain->default_account_id = $account->id;
        $domain->save();
        /*
         * Create user
         */
        $user = CreateUser::dispatchNow($this->request, $domain, $account, true); //make user company_owner
        /*
         * Required dependencies
         */
        if ($user) {
            auth()->login($user, false);
        }

        $user->setAccount($account);
        /*
         * Create token
         */ //$user_agent = isset($this->request['token_name']) ? $this->request['token_name'] : request()->server('HTTP_USER_AGENT');
//$company_token = CreateCompanyToken::dispatchNow($company, $user, $user_agent);
        /*
         * Fire related events
         */
        if ($user) {
            event(new DomainCreated($user));
        }

        $user->fresh();

        $account->notification(new NewAccountCreated($user, $account))->ninja();
        return $domain;
    }
}
