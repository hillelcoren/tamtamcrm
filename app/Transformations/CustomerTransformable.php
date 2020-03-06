<?php

namespace App\Transformations;

use App\Address;
use App\ClientContact;
use App\Customer;

trait CustomerTransformable
{
    protected function transformCustomer(Customer $customer)
    {

        $company = !empty($customer->company_id) ? $customer->company->toArray() : '';
        $credit = $customer->credits()->count() > 0 ? $customer->credits->first()->amount : 0;

        $addresses = $this->transformAddress($customer->addresses);


        $billing = null;
        $shipping = null;

        foreach ($addresses as $address) {

            if ($address->address_type === 1) {
                $billing = $address;
            } elseif ($address->address_type === 2) {
                $shipping = $address;
            }
        }

        $prop = new Customer;
        $prop->id = (int)$customer->id;
        $prop->created_at = $customer->created_at;
        $prop->name = $customer->name;
        $prop->phone = $customer->phone;
        $prop->company_id = $customer->company_id;
        $prop->deleted_at = $customer->deleted_at;
        $prop->company = $company;
        $prop->credit = $credit;
        $prop->contacts = $this->transformContacts($customer->contacts);
        $prop->default_payment_method = $customer->default_payment_method;
        $prop->group_settings_id = $customer->group_settings_id;
        $prop->shipping = $shipping;
        $prop->billing = $billing;
        $prop->website = $customer->website ?: '';
        $prop->industry_id = (int)$customer->industry_id ?: null;
        $prop->size_id = (int)$customer->size_id ?: null;
        $prop->currency_id = $customer->currency_id;
        $prop->paid_to_date = (float)$customer->paid_to_date;
        $prop->credit_balance = (float)$customer->credit_balance;
        $prop->balance = (float)$customer->balance;
        $prop->assigned_user = $customer->assigned_user_id;
        $prop->settings = [
            'payment_terms' => $customer->getSetting('payment_terms')
        ];
        $prop->custom_value1 = $customer->custom_value1 ?: '';
        $prop->custom_value2 = $customer->custom_value2 ?: '';
        $prop->custom_value3 = $customer->custom_value3 ?: '';
        $prop->custom_value4 = $customer->custom_value4 ?: '';
        $prop->private_notes = $customer->private_notes ?: '';
        $prop->public_notes = $customer->public_notes ?: '';

        return $prop;
    }

    /**
     * @param $contacts
     * @return array
     */
    private function transformContacts($contacts)
    {
        if (empty($contacts)) {
            return [];
        }

        return $contacts->map(function (ClientContact $contact) {
            return (new ContactTransformable())->transformClientContact($contact);
        })->all();
    }

    /**
     * @param $addresses
     * @return array
     */
    private function transformAddress($addresses)
    {
        if (empty($addresses)) {
            return [];
        }

        return $addresses->map(function (Address $address) {
            return (new AddressTransformable())->transformAddress($address);
        })->all();
    }
}
