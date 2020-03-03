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
use App\Invoice;
use App\Customer;
use App\Payment;

$factory->define(Payment::class, function (Faker\Generator $faker) {
    $customer = factory(Customer::class)->create();
    $user = factory(\App\User::class)->create();
    $invoice = factory(Invoice::class)->create();
    return [
        'user_id' => $user->id,
        'account_id' => 1,
        'is_deleted' => false,
        'amount' => $faker->numberBetween(1,10),
        'date' => $faker->date(),
        'transaction_reference' => $faker->text(10),
        'type_id' => 1,
        'status_id' => Payment::STATUS_COMPLETED,
        'customer_id' => $customer->id,
    ];
});
