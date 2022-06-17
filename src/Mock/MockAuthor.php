<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Author;
use Faker\Generator;

class MockAuthor implements Author
{
    public function __construct(Generator $faker)
    {
        $this->name = $faker->name;
        $this->contact = $faker->email;
    }
}
