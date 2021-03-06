<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Currency;
use Illuminate\Support\Collection;

interface CurrencyRepositoryInterface extends BaseRepositoryInterface
{
    public function listCurrencies(string $order = 'id', string $sort = 'desc'): Collection;
}
