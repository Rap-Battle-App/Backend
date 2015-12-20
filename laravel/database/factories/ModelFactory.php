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

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'city' => $faker->city,
        'about_me' => implode(' ', $faker->sentences($nb = 3)),
        'rapper' => true,
        'notifications' => (bool) rand(0, 1),
        'wins' => $faker->randomNumber(2),
        'rating' => $faker->randomNumber(2),
    ];
});

$factory->defineAs(App\Models\User::class, 'rapper', function (Faker\Generator $faker) use ($factory){
    $user = $factory->raw(App\Models\User::class);
    return array_merge($user, ['rapper' => true]);
});

$factory->defineAs(App\Models\User::class, 'non-rapper', function (Faker\Generator $faker) use ($factory){
    $user = $factory->raw(App\Models\User::class);
    return array_merge($user, ['rapper' => false]);
});
