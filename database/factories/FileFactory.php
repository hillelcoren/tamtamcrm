<?php

use Faker\Generator as Faker;

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
use App\File;
use App\Task;
use App\User;

$factory->define(File::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    $task = factory(Task::class)->create();

    return [
        'account_id' => 1,
        'is_active' => 1,
        'documentable_id' => $task->id,
        'documentable_type' => 'App\Task',
        'user_id' => $user->id,
        'name' => $faker->text,
        'file_path' => $faker->word,
        'preview' => null,
        'name' => null,
        'type' => null,
        'disk' => null,
        'hash' => null,
        'size' => null,
        'width' => null,
        'height' => null,
        'is_default' => 0,
        'documentable_id' => 0,
        'documentable_type' => '',
        'company_id' => null,
        'custom_value1' => null,
        'custom_value2' => null,
        'custom_value3' => null,
        'custom_value4' => null,
        'deleted_at' => null
    ];
});
