<?php

namespace App\Factory;

use Faker\Factory;
use Illuminate\Support\Carbon;
use stdClass;

//use Faker\Generator as Faker;

class InvoiceItemFactory
{
    public static function create(): stdClass
    {
        $item = new stdClass;
        $item->quantity = 0;
        $item->unit_cost = 0;
        $item->product_id = 1;
        $item->notes = '';
        $item->unit_discount = 0;
        $item->unit_tax = 0;
        $item->is_amount_discount = true;
        $item->sub_total = 0;
        $item->date = Carbon::now();
        return $item;
    }

    /**
     * Generates an array of dummy data for invoice items
     * @param int $items Number of line items to create
     * @return array        array of objects
     */
    public static function generate(int $items = 1): array
    {
        $faker = Factory::create();
        $data = [];
        for ($x = 0; $x < $items; $x++) {
            $item = self::create();
            $item->quantity = $faker->numberBetween(1, 10);
            $item->unit_cost = $faker->randomFloat(2, 1, 1000);
            $item->sub_total = $item->quantity * $item->unit_cost;
            $item->is_amount_discount = true;
            $item->unit_discount = $faker->numberBetween(1, 10);
            $item->unit_tax = 10.00;
            $item->notes = $faker->realText(50);

            $data[] = $item;
        }

        return $data;
    }

    /**
     * Generates an array of dummy data for invoice items
     * @param int $items Number of line items to create
     * @return array        array of objects
     */
    public static function generateCredit(int $items = 1): array
    {
        $faker = Factory::create();

        $data = [];

        for ($x = 0; $x < $items; $x++) {
            $item = self::create();
            $item->quantity = $faker->numberBetween(-1, -10);
            $item->unit_cost = $faker->randomFloat(2, -1, -1000);
            $item->sub_total = $item->quantity * $item->unit_cost;
            $item->is_amount_discount = true;
            $item->unit_discount = $faker->numberBetween(1, 10);
            $item->notes = $faker->realText(20);
            $item->product_key = $faker->word();
            $item->custom_value1 = $faker->realText(10);
            $item->custom_value2 = $faker->realText(10);
            $item->custom_value3 = $faker->realText(10);
            $item->custom_value4 = $faker->realText(10);
            $item->tax_name1 = 'GST';
            $item->unit_tax = 10.00;

            $data[] = $item;
        }

        return $data;
    }
}
