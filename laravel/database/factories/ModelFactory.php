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

$factory->define(App\Models\BattleRequest::class, function(Faker\Generator $faker){
    $user1 = factory(App\Models\User::class)->create();
    $user2 = factory(App\Models\User::class)->create();

    return ['challenger_id' => $user1->id,
            'challenged_id' => $user2->id];
});

$factory->define(App\Models\OpenBattle::class, function(Faker\Generator $faker){
    $user1 = factory(App\Models\User::class)->create();
    $user2 = factory(App\Models\User::class)->create();

    return ['rapper1_id' => $user1->id,
            'rapper2_id' => $user2->id,
            'phase' => rand(0,2)];
});

$factory->define(App\Models\Battle::class, function(Faker\Generator $faker){
    $user1 = factory(App\Models\User::class)->create();
    $user2 = factory(App\Models\User::class)->create();

    return ['rapper1_id' => $user1->id,
            'rapper2_id' => $user2->id,
            'votes_rapper1' => $faker->randomNumber(2),
            'votes_rapper2' => $faker->randomNumber(2)];
});
