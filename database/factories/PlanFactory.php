<?php

use Faker\Generator as Faker;
use App\Models\Plan\Plan;
use Illuminate\Support\Str;

$factory->define(Plan::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name
    ];
});

