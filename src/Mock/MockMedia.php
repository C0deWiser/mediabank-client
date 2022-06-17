<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Models\Media;
use Faker\Generator;

class MockMedia implements Media
{
    public function __construct(Generator $faker)
    {
        $this->id = $faker->numerify();
        $this->soap_status = 0;
        $this->type = 'photo';
        $this->duration = 0;
        $this->subject = $faker->boolean() ? $faker->sentence(2) : null;
        $this->caption = $faker->boolean() ? $faker->sentence(2) : null;
        $this->location = new MockLocation($faker);
        $this->datetime = $faker->dateTimeBetween()->format(\DateTime::ISO8601);
        $this->datetimeload = $faker->dateTimeBetween()->format(\DateTime::ISO8601);
        $this->author = new MockAuthor($faker);
        $this->shop = new MockGood($faker);
        $this->tags = $faker->randomElements([
            new MockTag($faker), new MockTag($faker), new MockTag($faker), new MockTag($faker),
            new MockTag($faker), new MockTag($faker), new MockTag($faker), new MockTag($faker),
        ], 3);
        $this->editor = $faker->name;
        $this->conditions = $faker->sentence;
        $this->instruction= $faker->sentence;
        $this->mime = "image/jpeg";
        $this->width = 5472;
        $this->height = 3648;
        $this->weight = 9542041;
        $this->sort = $faker->numerify('#');
        $this->iflag = $faker->boolean() ? $faker->boolean() : null;
        $this->md5 = md5($faker->sentence());
        $this->orig_name = basename($faker->filePath());
    }
}
