<?php

namespace App\Repositories\Interfaces;

use App\PaymentMethod;
use Illuminate\Support\Collection;

interface PaymentMethodRepositoryInterface
{

    /**
     *
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     */
    public function listPaymentMethods($columns = array('*'),
        string $orderBy = 'id',
        string $sortBy = 'asc'): Collection;

}
