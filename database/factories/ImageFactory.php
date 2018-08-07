<?php

use Faker\Generator as Faker;

$factory->define(\App\Image::class, function (Faker $faker) {
    return [
        'name' => $faker->text(80),
        'path' => $faker->imageUrl(),
        'post_id' => function () {
            return factory(\App\Post::class)->create()->id;
        },
    ];
});
