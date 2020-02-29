<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Design extends Model
{

    use SoftDeletes;

    protected $casts = [
        'design' => 'object'
    ];

    protected $fillable = [
        'name',
        'design',
        'is_active',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
