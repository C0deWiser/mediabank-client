<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Gallery;
use Faker\Generator;

class MockGallery implements Gallery
{
    public function __construct(Generator $faker)
    {
        $this->id = $faker->numerify();
        $this->soap_status = 0;
        $this->datetime = $faker->dateTimeBetween()->format(\DateTime::ISO8601);
        $this->author = $faker->boolean() ? $faker->name : null;
        $this->status = $faker->boolean(90);
        $this->category_id = $faker->numerify('##');
        $this->strict_cat = $faker->boolean(10);
        $this->caption = $faker->boolean() ? $faker->sentence() : null;
        $this->cover = $faker->numerify();
    }
}
