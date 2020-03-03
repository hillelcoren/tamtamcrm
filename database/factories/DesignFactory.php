<?php

use App\DataMapper\ClientSettings;
use App\DataMapper\CompanySettings;
use App\Factory\InvoiceItemFactory;
use Faker\Generator as Faker;

$factory->define(App\Design::class, function (Faker $faker) {
    $customer = factory(\App\Customer::class)->create();
    $user = factory(\App\User::class)->create();

    return [
        'account_id' => 1,
        'user_id' => $user->id,
        'is_deleted' => false,
        'name' => $this->faker->firstName,
        'design' => '<HTML></HTML'
    ];
});