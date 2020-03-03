<?php

namespace App\Presenters;

use App\Presenters\EntityPresenter;
use App\Utils\Number;

class CreditPresenter extends EntityPresenter
{
    public function customerName()
    {
        return $this->customer->present()->name();
    }

    public function address()
    {
        return $this->customer->present()->address();
    }

    public function shippingAddress()
    {
        return $this->customer->present()->shipping_address();
    }

    public function companyLogo()
    {
        return $this->customer->company->logo;
    }

    public function clientLogo()
    {
        return $this->client->logo;
    }

    public function companyName()
    {
        return $this->customer->company->present()->name();
    }

    public function companyAddress()
    {
        return $this->customer->company->present()->address();
    }

    public function clientName()
    {
        return $this->customer->present()->name();
    }
}
