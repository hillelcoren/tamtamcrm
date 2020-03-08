<?php

namespace App\Repositories;

use App\PaymentMethod;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Collection;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{

    /**
     * PaymentMethodRepository constructor.
     *
     * @param PaymentMethod $paymentMethod
     */
    public function __construct(PaymentMethod $paymentMethod)
    {
        parent::__construct($paymentMethod);
        $this->model = $paymentMethod;
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     *
     * @return Collection
     */
    public function listPaymentMethods(
        $columns = array('*'),
        string $orderBy = 'id',
        string $sortBy = 'asc'
    ): Collection
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

}
