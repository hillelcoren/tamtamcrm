<?php

namespace App\Presenters;

use App\Presenters\EntityPresenter;
use App\Utils\Number;

class QuotePresenter extends EntityPresenter
{
    public function customerName()
    {
        return $this->customer->present()->name();
    }
}
