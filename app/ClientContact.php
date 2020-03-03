<?php

namespace App;

use App\Account;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laracasts\Presenter\PresentableTrait;

class ClientContact extends Model implements HasLocalePreference
{
    use PresentableTrait;
    use SoftDeletes;

    protected $presenter = 'App\Presenters\ClientContactPresenter';

    protected $dates = [
        'deleted_at'
    ];

    protected $with = [
        //'customer',
        //'account'
    ];

    protected $casts = [
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    protected $hidden = [
        'user_id',
        'account_id',
        'customer_id',
        'token',
        'password',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'email',
        'is_primary',
        'password'
    ];

    /**/
    public function getRouteKeyName()
    {
        return 'contact_id';
    }

    public function preferredLocale()
    {
        $languages = Language::all();

        return $languages->filter(function ($item) {
            return $item->id == $this->customer->getSetting('language_id');
        })->first()->locale;

        //$lang = Language::find($this->client->getSetting('language_id'));

        //return $lang->locale;
    }

    public function setAvatarAttribute($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL) && $value) {
            $this->attributes['avatar'] = url('/') . $value;
        } else {
            $this->attributes['avatar'] = $value;
        }
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function primary_contact()
    {
        return $this->where('is_primary', true);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
