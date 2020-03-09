<?php

namespace App;

use App\AccountUser;
use App\Util\Jobs\FileUploader;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Event;
use App\Traits\HasPermissionsTrait;
use Laratrust\Traits\LaratrustUserTrait;
use App\Message;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Department;
use App\Role;
use App\Permission;
use App\Account;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{

    use LaratrustUserTrait;
    use Notifiable, SoftDeletes, HasPermissionsTrait, PresentableTrait;
    use HasRelationships;

    protected $presenter = 'App\Presenters\UserPresenter';

    protected $with = ['accounts'];

    public $account;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'profile_photo',
        'username',
        'email',
        'password',
        'role_id',
        'job_description',
        'dob',
        'phone_number',
        'gender',
        'auth_token',
        'account',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'is_active'
    ];

    public function events()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsToMany
     */
    public function messages()
    {
        return $this->belongsToMany(Message::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function department_manager()
    {
        return $this->morphTo();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     *
     * As we are authenticating on CompanyToken,
     * we need to link the company to the user manually. This allows
     * us to decouple a $user and their attached companies.
     *
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * Returns the currently set Company
     */
    public function getAccount()
    {
        if ($this->account) {
            return $this->account;
        }
    }

    /**
     * Returns the current company
     *
     * @return Collection
     */
    public function account()
    {
        return $this->getAccount();
    }

    public function account_users()
    {
        return $this->hasMany(AccountUser::class);
    }

    public function documents()
    {
        return $this->morphMany(File::class, 'documentable');
    }

    public function account_user()
    {
        if (!$this->id) {
            $this->id = auth()->user()->id;
        }

        /*
        select `accounts`.*, `accounts`.`id` as `account_id`
        from `company_tokens`
        inner join accounts on accounts.id = company_tokens.account_id
        where company_tokens.user_id = 9874 and `company_tokens`.`token` = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC90YXNrbWFuMi5kZXZlbG9wXC9hcGlcL2xvZ2luIiwiaWF0IjoxNTgwNTc2ODI5LCJleHAiOjE1ODA1ODA0MjksIm5iZiI6MTU4MDU3NjgyOSwianRpIjoicjVxN3pudXdISjFGVWo1dCIsInN1YiI6OTg3NCwicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOT'
        */

        return AccountUser::join('company_tokens', 'company_tokens.account_id', '=', 'account_user.account_id')
                          ->where('company_tokens.user_id', '=', $this->id)
                          ->where('company_tokens.token', '=', $this->auth_token)->select('account_user.*')->first();
    }

    /**
     * Returns a boolean of the administrator status of the user
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->account_user->is_admin;
    }

    public function isOwner(): bool
    {
        return $this->account_user->is_owner;
    }

    /**
     * Returns the currently set company id for the user
     *
     * @return int
     */
    public function accountId(): int
    {
        return $this->account()->id;

    }

    /**
     * Returns all companies a user has access to.
     *
     * @return Collection
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class)->using(AccountUser::class)
                    ->withPivot('permissions', 'settings', 'is_admin', 'is_owner', 'is_locked');
    }

    // Example, just to showcase the API.
    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }


    public function avatar()
    {
        // Maybe even wrapper arround this search/find API won't be bad. This is raw example.

        $avatar = $this->uploads()->where('type', FileUploader::AVATAR)->first();

        if ($avatar) {

            $path = sprintf('%s/%s.%s', FileUploader::PROPERTIES[FileUploader::AVATAR]['path'], $avatar->uuid,
                $avatar->extension);

            return Storage::disk($avatar->disk)->url($path);
        }

        return '404. Provide some generic gravatar or default image.';
    }

    public function routeNotificationForSlack($notification)
    {
        //todo need to return the company channel here for hosted users
        //else the env variable for selfhosted
        if (config('taskmanager.environment') == 'selfhosted') {
            return config('taskmanager.notification.slack');
        }

        if ($this->account()) {
            return $this->account()->settings->system_notifications_slack;
        }
    }


    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
}
