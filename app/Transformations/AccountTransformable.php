<?php

namespace App\Transformations;

use App\Account;
use stdClass;

trait AccountTransformable
{

    /**
     * @param Account $account
     * @return Address
     */
    public function transformAccount(Account $account)
    {
        $obj = new Account;
        $obj->id = (int)$account->id;
        $std = new stdClass;

        $obj->update_products = (bool)$account->update_products;
        $obj->fill_products = (bool)$account->fill_products;
        $obj->convert_products = (bool)$account->convert_products;
        $obj->custom_surcharge_taxes1 = (bool)$account->custom_surcharge_taxes1;
        $obj->custom_surcharge_taxes2 = (bool)$account->custom_surcharge_taxes2;
        $obj->custom_surcharge_taxes3 = (bool)$account->custom_surcharge_taxes3;
        $obj->custom_surcharge_taxes4 = (bool)$account->custom_surcharge_taxes4;
        $obj->show_product_cost = (bool)$account->show_product_cost;
        $obj->enable_invoice_quantity = (bool)$account->enable_invoice_quantity;
        $obj->enable_product_cost = (bool)$account->enable_product_cost;
        $obj->show_product_details = (bool)$account->show_product_details;
        $obj->enable_product_quantity = (bool)$account->enable_product_quantity;
        $obj->default_quantity = (bool)$account->default_quantity;
        $obj->custom_fields = $account->custom_fields ?: $std;
        $obj->size_id = (string)$account->size_id ?: '';
        $obj->industry_id = (string)$account->industry_id ?: '';
        $obj->first_month_of_year = (string)$account->first_month_of_year ?: '';
        $obj->first_day_of_week = (string)$account->first_day_of_week ?: '';
        $obj->subdomain = (string)$account->subdomain ?: '';
        $obj->portal_mode = (string)$account->portal_mode ?: '';
        $obj->portal_domain = (string)$account->portal_domain ?: '';
        $obj->settings = $account->settings ?: '';
        $obj->enabled_tax_rates = (int)$account->enabled_tax_rates;
        $obj->updated_at = (int)$account->updated_at;
        $obj->deleted_at = (int)$account->deleted_at;
        $obj->slack_webhook_url = (string)$account->slack_webhook_url;
        $obj->google_analytics_url = (string)$account->google_analytics_url;
        return $obj;
    }

}
