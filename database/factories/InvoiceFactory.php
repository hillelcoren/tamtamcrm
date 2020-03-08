<?php

use App\Invoice;
use App\Customer;
use App\User;

$factory->define(Invoice::class, function (Faker\Generator $faker) {

    $customer = factory(Customer::class)->create();
    $user = factory(User::class)->create();

    return [
        'account_id' => 1,
        'status_id' => Invoice::STATUS_SENT,
        'number' => $faker->ean13(),
        'total' => $faker->randomFloat(),
        'tax_total' => $faker->randomFloat(),
        'discount_total' => $faker->randomFloat(),
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'is_deleted' => false,
        'po_number' => $faker->text(10),
        'date' => $faker->date(),
        'due_date' => $faker->date(),
        'line_items' => \App\Factory\InvoiceItemFactory::generate(5),
        'terms' => $faker->text(500),
    ];
});
