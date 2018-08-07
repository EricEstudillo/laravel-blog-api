<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(App\Post::class, function (Faker $faker) {
    $title = $faker->text(20);
    if (Auth::check()) {
        $userId = Auth::id();
    } else {
        $userId = function () {
            return factory(\App\User::class)->create()->id;
        };
    }
    return [
        'title' => $title,
        'slug' => str_slug($title),
        'body' => $faker->text(100),
        'user_id' => $userId
    ];
});
