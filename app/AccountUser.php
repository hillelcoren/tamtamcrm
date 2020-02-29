<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountUser extends Pivot
{
    //   protected $guarded = ['id'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'settings' => 'object',
    ];
    protected $fillable = [
        'account_id',
        'permissions',
        'settings',
        'is_admin',
        'is_owner',
        'is_locked'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user_pivot()
    {
        return $this->hasOne(User::class)->withPivot('permissions', 'settings', 'is_admin', 'is_owner', 'is_locked');
    }

    public function account_pivot()
    {
        return $this->hasOne(Account::class)->withPivot('permissions', 'settings', 'is_admin', 'is_owner', 'is_locked');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
