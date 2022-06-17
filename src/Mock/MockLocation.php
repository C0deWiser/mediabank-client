<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Location;
use Faker\Generator;

class MockLocation implements Location
{
    public function __construct(Generator $faker)
    {
        $this->country = $faker->country();
        $this->address = $faker->address();
        $this->region = null;
    }
}
