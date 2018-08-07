<?php

use Faker\Generator as Faker;

$factory->define(App\Tag::class, function (Faker $faker) {
    $name = $faker->text(20);
    return [
        'name'=> $name,
    ];
});
