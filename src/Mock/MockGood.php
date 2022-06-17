<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Good;
use Faker\Generator;

class MockGood implements Good
{
    public function __construct(Generator $faker)
    {
        $this->stockNumber = $faker->creditCardNumber;
        $this->view = $faker->slug(1);
        $this->color = $faker->colorName();
    }
}
