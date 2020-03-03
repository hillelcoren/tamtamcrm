<?php

/*
  |--------------------------------------------------------------------------
  | Model Factories
  |--------------------------------------------------------------------------
  |
  | Here you may define all of your model factories. Model factories give
  | you a convenient way to create models for testing and seeding your
  | database. Just tell the factory how a default model should look.
  |
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Customer;
use App\Company;
use App\Account;
use App\User;

$factory->define(Customer::class, function (Faker\Generator $faker) {

    $company = factory(Company::class)->create();
    $user = factory(User::class)->create();
    //$account = factory(Account::class)->create();

    return [
        'name' => $faker->name(),
        'website' => $faker->url,
        'private_notes' => $faker->text(200),
        'balance' => 0,
        'paid_to_date' => 0,
        'custom_value1' => $faker->text(20),
        'custom_value2' => $faker->text(20),
        'custom_value3' => $faker->text(20),
        'custom_value4' => $faker->text(20),
        'settings' => \App\DataMapper\CustomerSettings::defaults(),
        'account_id' => 1,
        'user_id' => $user->id,
        'phone' => $faker->phoneNumber,
        'status' => 1

    ];
});
