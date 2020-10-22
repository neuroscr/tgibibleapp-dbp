<?php

use Faker\Generator as Faker;

use App\Models\User\User;
use App\Models\Collection\Collection;

// Project Factory
$factory->define(Collection::class, function (Faker $faker) {
    return [
        // this is auto increment, we don't need to set it
        // random can cause conflicts
        //'id'               => random_int(0, 9999),
        //'id'               => factory(User::class)->create()->id,
        'name'             => $faker->name,
        //'deleted_at'       => null
    ];
});
