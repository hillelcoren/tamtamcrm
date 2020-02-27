<?php

namespace App;

use App\Customer;
use App\Account;
use App\CompanyGateway;
use App\GatewayType;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ClientGatewayToken extends Model
{
    protected $casts = [
        'meta' => 'object',
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    public function client()
    {
        return $this->hasOne(Customer::class)->withTrashed();
    }

    public function gateway()
    {
        return $this->hasOne(CompanyGateway::class);
    }

    public function gateway_type()
    {
        return $this->hasOne(GatewayType::class, 'id', 'gateway_type_id');
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function user()
    {
        return $this->hasOne(User::class)->withTrashed();
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @return Model|null
     */
    public function resolveRouteBinding($value)
    {
        return $this->where('id', $value)->firstOrFail();
    }
}
