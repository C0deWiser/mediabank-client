<?php

namespace Codewiser\Mediabank\Client\Mock;

use Codewiser\Mediabank\Client\Contracts\SoapContract;
use Codewiser\Mediabank\Client\Exceptions\SoapException;
use Codewiser\Mediabank\Client\Models\Gallery;
use Codewiser\Mediabank\Client\Models\Media;
use Codewiser\Mediabank\Client\Models\Update;
use Faker\Factory;
use RuntimeException;

class MockSoap implements SoapContract
{
    protected $faker;
    protected $authorized = false;
    protected $locale = 'ru';

    /**
     * @var MockBlueprint|null
     */
    protected $blueprint = null;

    public function __construct(MockBlueprint $blueprint)
    {
        $this->blueprint = $blueprint;

        $this->faker = Factory::create($this->locale);
    }

    public function Login(string $login, string $password, string $locale): bool
    {
        $this->authorized = true;

        $this->locale = $locale;

        $this->faker = Factory::create($this->locale);

        return $this->authorized;
    }

    /**
     * @throws SoapException
     */
    protected function checkAuthorization(): MockSoap
    {
        if (!$this->authorized) {
            throw new SoapException("Unauthorized");
        }

        return $this;
    }

    protected function blueprint(): MockBlueprint
    {
        $blueprint = $this->blueprint;

        if (!$blueprint) {
            throw new RuntimeException(MockBlueprint::class . ' required');
        }

        return $blueprint;
    }

    /**
     * @throws SoapException
     */
    protected function galleryShouldExist(int $gallery_id): MockBlueprint
    {
        $blueprint = $this->blueprint();

        if ($blueprint->getId() != $gallery_id) {
            throw new SoapException("Gallery $gallery_id Not Found");
        }

        return $blueprint;
    }

    /**
     * @throws SoapException
     */
    protected function mediaShouldExist($media_id): MockBlueprint
    {
        $blueprint = $this->blueprint();

        $filtered = array_filter(
            $blueprint->getMedia(),
            function (MockBlueprint $media) use ($media_id) {
                return $media->getId() == $media_id;
            }
        );
        if (!$filtered) {
            throw new SoapException("Media $media_id Not Found");
        }

        return current($filtered);
    }

    public function Language(string $locale): void
    {
        $this->locale = $locale;

        $this->faker = Factory::create($this->locale);
    }

    /**
     * @return array<Update>
     * @throws SoapException
     */
    public function GalleryList(): array
    {
        $gallery = $this
            ->checkAuthorization()
            ->blueprint();

        $update = new MockUpdate();
        $update->id = $gallery->getId();
        $update->status = $gallery->getStatus();

        return [$update];
    }

    /**
     * @return array<Update>
     * @throws SoapException
     */
    public function GalleryListByDate(int $dateAfter, int $dateBefore = 0): array
    {
        return $this
            ->checkAuthorization()
            ->GalleryList();
    }

    /**
     * @param int $gallery_id
     * @return Gallery
     * @throws SoapException
     */
    public function GalleryInfo(int $gallery_id): Gallery
    {
        $gallery = $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        $info = new MockGallery($this->faker);
        $info->id = $gallery->getId();
        $info->soap_status = $gallery->getStatus();

        return $info;
    }

    /**
     * @param int $gallery_id
     * @param int $width
     * @param int $height
     * @param string $algorithm
     * @return string
     * @throws SoapException
     */
    public function GalleryCover(int $gallery_id, int $width = 0, int $height = 0, string $algorithm = 'crop'): string
    {
        $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        $width = $width || 640;
        $height = $height || 480;
        return "https://picsum.photos/$width/$height";
    }

    /**
     * @throws SoapException
     */
    public function File(int $media_id, string $ext = null): string
    {
        $this
            ->checkAuthorization()
            ->mediaShouldExist($media_id);

        return "https://picsum.photos/1920/1280";
    }

    /**
     * @throws SoapException
     */
    public function Thumbnail(int $media_id, int $width = 0, int $height = 0, string $algorithm = ''): string
    {
        $this
            ->checkAuthorization()
            ->mediaShouldExist($media_id);

        $width = $width || 640;
        $height = $height || 480;
        return "https://picsum.photos/$width/$height";
    }

    /**
     * @throws SoapException
     */
    public function Finish(int $gallery_id, int $media_id = null): bool
    {
        $gallery = $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        if ($media_id) {
            $this
                ->mediaShouldExist($media_id)
                ->setFinished();
            return true;
        }

        $unfinished = array_filter(
            $gallery->getMedia(),
            function (MockBlueprint $item) {
                return !$item->isFinished();
            }
        );

        if ($unfinished) {
            throw new SoapException("Cant Finish Gallery $gallery_id. Some Media Not Finished");
        }

        $gallery->setFinished();

        return true;
    }

    /**
     * @throws SoapException
     */
    public function reportGalleryPath(int $gallery_id, string $url): void
    {
        $gallery = $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        $gallery->setPath($url);
    }

    /**
     * @throws SoapException
     */
    public function CategoryList(array $categories): void
    {
        $this
            ->checkAuthorization()
            ->blueprint()
            ->setCategories($categories);
    }

    /**
     * @param int $gallery_id
     * @param bool $all
     * @return array<Update>
     * @throws SoapException
     */
    public function ItemList(int $gallery_id, bool $all = false): array
    {
        $gallery = $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        $items = $gallery->getMedia();

        if (!$all) {
            $items = array_filter($items, function (MockBlueprint $item) {
                return $item->getStatus() > 0;
            });
        }

        $updates = [];

        foreach ($items as $item) {
            $update = new MockUpdate();
            $update->id = $item->getId();
            $update->status = $item->getStatus();

            $updates[] = $update;
        }

        return $updates;
    }

    /**
     * @throws SoapException
     */
    public function ItemInfo(int $media_id, int $gallery_id): Media
    {
        $this
            ->checkAuthorization()
            ->galleryShouldExist($gallery_id);

        $media = $this->mediaShouldExist($media_id);

        $info = new MockMedia($this->faker);
        $info->id = $media->getId();
        $info->soap_status = $media->getStatus();

        return $info;
    }
}
