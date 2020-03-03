<?php

namespace App\Presenters;

use App\Country;

/**
 * Class CustomerPresenter
 * @package App\Presenters
 */
class LeadPresenter extends EntityPresenter
{
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function name()
    {
        return $this->entity->first_name . ' ' . $this->entity->last_name;

    }

    public function email()
    {
        return $this->entity->email;
    }

    public function phone()
    {
        return $this->entity->phone ?: '';
    }
}
