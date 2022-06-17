<?php

namespace Codewiser\Mediabank\Client\Mock;

use ArrayObject;
use Faker\Factory;

class MockBlueprint
{
    protected $faker;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     */
    protected $status = 0;

    /**
     * @var string|null
     */
    protected $path = null;

    protected $finished = false;

    /**
     * @var array<MockBlueprint>
     */
    protected $items;

    /**
     * @var array|null
     */
    protected $categories = null;

    public function __construct(int $status = 0)
    {
        $this->faker = Factory::create();

        $this->setStatus($status);

        $this->items = [];
    }

    public function getId(): ?int
    {
        if (!$this->id) {
            $this->id = $this->faker->numerify();
        }

        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function addMedia(MockBlueprint $blueprint): void
    {
        $this->items[] = $blueprint;
    }

    /**
     * @return array<MockBlueprint>
     */
    public function getMedia(): array
    {
        return $this->items;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished = true): void
    {
        $this->finished = $finished;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return array|null
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    /**
     * @param array|null $categories
     */
    public function setCategories(?array $categories): void
    {
        $this->categories = $categories;
    }

}
