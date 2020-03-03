<?php

namespace App\Repositories;

use App\ClientContact;
use App\Design;
use App\Models\Client;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use App\Customer;
use Exception;
use Illuminate\Support\Collection as Support;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesCounter;
use App\Factory\CustomerFactory;

/**
 * Class DesignRepository
 * @package App\Repositories
 */
class DesignRepository extends BaseRepository
{

    /**
     * CustomerRepository constructor.
     * @param Customer $customer
     */
    public function __construct(Design $design)
    {
        parent::__construct($design);
        $this->model = $design;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $id
     * @return Lead
     */
    public function findDesignById(int $id): Design
    {
        return $this->findOneOrFail($id);
    }

}
