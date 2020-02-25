<?php

namespace App\Jobs\User;

use App\DataMapper\DefaultSettings;
use App\Events\User\UserWasCreated;
use App\AccountUser;
use App\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateUser
{
    use Dispatchable;

    protected $request;
    protected $domain;
    protected $account;
    protected $account_owner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request, $domain, $account, $account_owner = false)
    {
        $this->request = $request;
        $this->domain = $domain;
        $this->account = $account;
        $this->account_owner = $account_owner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): ?User
    {
        $user = new User();
        $user->password = bcrypt($this->request['password']);
        $user->fill($this->request);
        $user->email = $this->request['email'];//todo need to remove this in production
        //$user->last_login = now();
        //$user->ip = request()->ip();
        $user->save();
        $user->accounts()->attach($this->account->id, [
            'domain_id' => $this->domain->id,
            'is_owner' => $this->account_owner,
            'is_admin' => 1,
            'is_locked' => 0,
            'permissions' => '',
            //'settings' => DefaultSettings::userSettings(),
        ]);
        event(new UserWasCreated($user, $this->account));
        return $user;
    }
}
