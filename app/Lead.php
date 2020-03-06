<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 27/02/2020
 * Time: 19:50
 */

namespace App;


use App\Services\Lead\LeadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

class Lead extends Model
{
    use SoftDeletes;
    use PresentableTrait;

    protected $presenter = 'App\Presenters\LeadPresenter';
    const STATUS_COMPLETED = 100;

    protected $fillable = [
        'account_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_1',
        'address_2',
        'zip',
        'city',
        'job_title',
        'company_name',
        'description',
        'title',
        'valued_at',
        'source_type'
    ];

    public function service(): LeadService
    {
        return new LeadService($this);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
