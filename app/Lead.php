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

class Lead extends Model
{
    protected $fillable = [
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

}