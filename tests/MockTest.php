<?php

namespace Codewiser\Mediabank\Tests;

use Codewiser\Mediabank\Client\Exceptions\SoapException;
use Codewiser\Mediabank\Client\Mock\MockBlueprint;
use Codewiser\Mediabank\Client\Mock\MockSoap;
use Codewiser\Mediabank\Client\Models\Update;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class MockTest extends TestCase
{
    protected $faker;
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGalleryId()
    {
        $blueprint = new MockBlueprint(Update::NEW);
        $blueprint->addMedia(new MockBlueprint(Update::NEW));
        $blueprint->addMedia(new MockBlueprint(Update::NEW));

        $mock = new MockSoap($blueprint);
        $mock->Login(1, 2, 'ru');

        $updates = $mock->GalleryList();
        $this->assertCount(1, $updates);

        $this->assertEquals($updates[0]->id, $blueprint->getId());
        $this->assertEquals($updates[0]->status, $blueprint->getStatus());
    }
    public function testGalleryCantFinish()
    {
        $blueprint = new MockBlueprint(Update::NEW);
        $blueprint->addMedia(new MockBlueprint(Update::NEW));
        $blueprint->addMedia(new MockBlueprint(Update::NEW));

        $mock = new MockSoap($blueprint);
        $mock->Login(1, 2, 'ru');

        // Нельзя финишировать галерею до финиша всех медиа из неё.
        $this->expectException(SoapException::class);
        $mock->Finish($blueprint->getId());
    }
    public function testGalleryInfoNotFound()
    {
        $blueprint = new MockBlueprint(Update::NEW);
        $blueprint->addMedia(new MockBlueprint(Update::NEW));
        $blueprint->addMedia(new MockBlueprint(Update::NEW));

        $mock = new MockSoap($blueprint);
        $mock->Login(1, 2, 'ru');

        // Галерея не найдена.
        $this->expectException(SoapException::class);
        $mock->GalleryInfo($this->faker->numerify());
    }

    public function testGalleryFinishNotFound()
    {
        $blueprint = new MockBlueprint(Update::NEW);
        $blueprint->addMedia(new MockBlueprint(Update::NEW));
        $blueprint->addMedia(new MockBlueprint(Update::NEW));

        $mock = new MockSoap($blueprint);
        $mock->Login(1, 2, 'ru');

        // Галерея не найдена.
        $this->expectException(SoapException::class);
        $mock->Finish($this->faker->numerify());
    }
}
