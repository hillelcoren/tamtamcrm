<?php

use Faker\Generator as Faker;
use App\User;
use App\Task;
use App\Customer;
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
use App\Comment;

$factory->define(Comment::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    $task = factory(Task::class)->create();

    return [
        'account_id' => 1,
        'parent_id' => 0,
        'is_active' => 1,
        'user_id' => $user->id,
        'parent_type' => 1,
        'comment' => $faker->text,
    ];
});
