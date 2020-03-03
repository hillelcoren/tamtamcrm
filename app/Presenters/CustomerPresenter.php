<?php

namespace App\Presenters;

use App\Country;

/**
 * Class CustomerPresenter
 * @package App\Presenters
 */
class CustomerPresenter extends EntityPresenter
{
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function name()
    {
        $contact = $this->entity->primary_contact->first();

        $contact_name = 'No Contact Set';

        if ($contact) {
            $contact_name = $contact->first_name . ' ' . $contact->last_name;
        }

        return $this->entity->name ?: $contact_name;
    }

    public function primary_contact_name()
    {
        return $this->entity->primary_contact->first() !== null ? $this->entity->primary_contact->first()->first_name .
            ' ' . $this->entity->primary_contact->first()->last_name : 'No primary contact set';
    }

    public function email()
    {
        return $this->entity->primary_contact->first() !==
        null ? $this->entity->primary_contact->first()->email : 'No Email Set';
    }

    public function address()
    {
        if ($this->entity->addresses->count() === 0) {
            return false;
        }

        $str = '';
        $client = $this->entity->addresses->first();
        if ($address1 = $client->address_1) {
            $str .= $address1 . '<br/>';
        }
        if ($address2 = $client->address_2) {
            $str .= e($address2) . '<br/>';
        }
        if ($city = $this->city) {
            $str .= e($city) . '<br/>';
        }
        if ($country = $client->country) {
            $str .= e($country->name) . '<br/>';
        }

        return $str;
    }

    public function shipping_address()
    {
        $str = '';
        $client = $this->entity;
        if ($address1 = $client->address1) {
            $str .= e($address1) . '<br/>';
        }
        if ($address2 = $client->address2) {
            $str .= e($address2) . '<br/>';
        }
        if ($city = $this->city) {
            $str .= e($city) . '<br/>';
        }
        if ($country = $client->country) {
            $str .= e($country->name) . '<br/>';
        }
        return $str;
    }

    public function phone()
    {
        return $this->entity->phone ?: '';
    }

    public function website()
    {
        return $this->entity->website ?: '';
    }

    /**
     * Calculated company data fields
     * using settings
     */
    public function company_name()
    {
        $settings = $this->entity->company;
        return $settings->name ?: 'Not set';
    }

    public function company_address()
    {
        $settings = $this->entity;
        $str = '';

        if ($settings->address_1) {
            $str .= e($settings->address_1) . '<br/>';
        }
        if ($settings->address_2) {
            $str .= e($settings->address_2) . '<br/>';
        }
        if ($cityState = $settings->city) {
            $str .= e($cityState) . '<br/>';
        }
        if ($country = Country::find($settings->country_id)) {
            $str .= e($country->name) . '<br/>';
        }
        return $str;
    }

    public function getCityState()
    {
        $settings = $this->entity->getMergedSettings();
        $country = false;
        if ($settings->country_id) {
            $country = Country::find($settings->country_id);
        }
        $swap = $country && $country->swap_postal_code;
        $city = e($settings->city ?: '');
        $state = e($settings->state ?: '');
        $postalCode = e($settings->postal_code ?: '');
        if ($city || $state || $postalCode) {
            return $this->cityStateZip($city, $state, $postalCode, $swap);
        } else {
            return false;
        }
    }
}
