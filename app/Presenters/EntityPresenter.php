<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;
use stdClass;

/**
 * Class EntityPresenter
 * @package App\Models\Presenters
 */
class EntityPresenter extends Presenter
{
    /**
     * @return string
     */
    public function id()
    {
        return $this->entity->id;
    }

    /**
     *
     */
    public function url()
    {
    }

    /**
     *
     */
    public function path()
    {
    }

    /**
     *
     */
    public function editUrl()
    {
    }

    /**
     * @param bool $label
     */
    public function statusLabel($label = false)
    {
    }

    /**
     *
     */
    public function statusColor()
    {
    }

    /**
     *
     */
    public function link()
    {
    }

    /**
     *
     */
    public function titledName()
    {
    }

    /**
     * @param bool $subColors
     */
    public function calendarEvent($subColors = false)
    {
    }

    public function getCityState()
    {
        $client = $this->entity;
        $swap = $client->country && $client->country->swap_postal_code;

        $city = e($client->city);
        $state = e($client->state);
        $postalCode = e($client->postal_code);

        if ($city || $state || $postalCode) {
            return $this->cityStateZip($city, $state, $postalCode, $swap);
        } else {
            return false;
        }
    }

    public function getShippingCityState()
    {
        $client = $this->entity;
        $swap = $client->shipping_country && $client->shipping_country->swap_postal_code;

        $city = e($client->shipping_city);
        $state = e($client->shipping_state);
        $postalCode = e($client->shipping_postal_code);

        if ($city || $state || $postalCode) {
            return $this->cityStateZip($city, $state, $postalCode, $swap);
        } else {
            return false;
        }
    }

    public function cityStateZip($city, $state, $postalCode, $swap)
    {
        $str = $city;

        if ($state) {
            if ($str) {
                $str .= ', ';
            }
            $str .= $state;
        }

        if ($swap) {
            return $postalCode . ' ' . $str;
        } else {
            return $str . ' ' . $postalCode;
        }
    }

    public function clientName()
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
        return $this->account->logo;
    }

    public function clientLogo()
    {
        return $this->customer->logo;
    }

    public function companyName()
    {
        return $this->account->present()->name();
    }

    public function companyAddress()
    {
        return $this->account->present()->address();
    }

}
