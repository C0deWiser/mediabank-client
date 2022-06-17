<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Tag;
use Faker\Generator;

class MockTag implements Tag
{
    public function __construct(Generator $faker)
    {
        $this->id = $faker->numerify();
        $this->zenit_id = $faker->numerify();
        $this->name = $faker->slug('1');
        $this->type = $faker->slug('1');
    }
}
