<?php


namespace Codewiser\Mediabank\Client;

use Carbon\Carbon;
use Closure;
use Psr\Log\LoggerInterface;
use SoapFault;

class MediabankService implements Contracts\MediabankContract
{
    /**
     * SOAP клиент.
     *
     * @var Models\Client
     */
    protected $soap;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $locales = [];

    public function __construct(Models\Client $soap, array $locales, LoggerInterface $logger)
    {
        $this->soap = $soap;
        $this->locales = $locales;
        $this->logger = $logger;

        $this->logger->debug(__METHOD__, $locales);

        if (count($locales) == 0) {
            throw new \RuntimeException(__CLASS__ . ' требует хотя бы один язык.');
        }
    }

    /**
     * Get Update bitmask.
     */
    protected function bitmask(int $status): array
    {
        $mask = [];

        $bits = [
            Models\Update::TRANSLATED,
            Models\Update::DELETE,
            Models\Update::UPDATE,
            Models\Update::CAPTION,
            Models\Update::NEW,
        ];

        foreach ($bits as $bit) {
            if ($status >= $bit) {
                $mask[] = $bit;
                $status -= $bit;
            }
        }

        if (!$mask) {
            $mask[] = Models\Update::OK;
        }

        return $mask;
    }

    /**
     * @param Contracts\GalleryImporterContract|Closure $importer
     * @return int
     * @throws Exceptions\SoapException
     */
    public function fetchUpdates($importer): int
    {
        $this->logger->notice(__METHOD__);

        $galleries = $this->soap->GalleryList();

        $this->logger->notice(__METHOD__ . ' ' . count($galleries) . ' updates');

        foreach ($galleries as $gallery) {
            if ($importer instanceof Closure) {
                call_user_func($importer, $gallery);
            } else {
                $this->importGallery($importer, $gallery);
            }
        }

        return count($galleries);
    }

    public function fetchGalleries(Contracts\GalleryImporterContract $importer, Carbon $after, Carbon $before = null): int
    {
        $this->logger->notice(__METHOD__, [
            'after' => $after,
            'before' => $before
        ]);

        $galleries = $this->soap->GalleryListByDate($after->getTimestamp(), $before ? $before->getTimestamp() : null);

        foreach ($galleries as $gallery) {
            $this->importGallery($importer, $gallery, false);
        }

        return count($galleries);
    }

    /**
     * @param Models\Update $gallery
     * @throws Exceptions\SoapException
     */
    protected function importGallery(Contracts\GalleryImporterContract $importer, $gallery, bool $force = true)
    {
        if (!$force && !$importer->shouldImport($gallery->id)) {
            // Если нет указания импортировать насильно,
            // и галерея у нас уже есть,
            // то пропускаем её.
            return;
        }

        $bits = $this->bitmask($gallery->status);

        $this->logger->debug(__METHOD__, ['gallery' => $gallery->id, 'bits' => $bits]);

        if (in_array(Models\Update::DELETE, $bits)) {

            $this->revokeGallery($importer, $gallery->id);
            $this->finishGallery($importer, $gallery->id, true);

        } else {

            $this->updateGallery($importer, $gallery->id);
            $this->coverGallery($importer, $gallery->id);

            foreach ($this->soap->ItemList($gallery->id, $force) as $media) {
                $this->importMedia($importer, $gallery->id, $media);
            }

            $this->revokeObsolete($importer, $gallery->id);
            $this->consummateGallery($importer, $gallery->id);
            $this->finishGallery($importer, $gallery->id);
        }
    }

    /**
     * @param Models\Update $media
     * @throws Exceptions\SoapException
     */
    protected function importMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, $media)
    {
        $bits = $this->bitmask($media->status);

        $this->logger->debug(__METHOD__, ['media' => $media->id, 'bits' => $bits]);

        if (in_array(Models\Update::DELETE, $bits)) {

            $this->revokeMedia($importer, $gallery_id, $media->id);

        } else {

            $this->updateMedia($importer, $gallery_id, $media->id);
            $this->coverMedia($importer, $gallery_id, $media->id);
            $this->sourceMedia($importer, $gallery_id, $media->id);
            $this->consummateMedia($importer, $gallery_id, $media->id);

        }

        $this->finishMedia($importer, $gallery_id, $media->id);
    }

    protected function finishMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        if ($this->soap->Finish($gallery_id, $id)) {
            $this->logger->notice(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);
        } else {
            $this->logger->alert(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);
        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function consummateMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {

        $consummated = $importer
            ->with($gallery_id)
            ->finish($id);

        if (!$consummated) {

            $this->logger->alert(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);

            throw new Exceptions\SoapException("Couldn't consummate media {$id}");
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $gallery_id,
            'media' => $id
        ]);
    }

    protected function finishGallery(Contracts\GalleryImporterContract $importer, int $id, bool $withAllItems = false)
    {
        if ($withAllItems) {
            // Finish all
            foreach ($this->soap->ItemList($id) as $media) {
                $this->finishMedia($importer, $id, $media->id);
            }
        }

        if ($this->soap->Finish($id)) {
            $this->logger->notice(__METHOD__, [
                'gallery' => $id
            ]);
        } else {
            $this->logger->alert(__METHOD__, [
                'gallery' => $id
            ]);
        }

        if ($url = $importer->route($id)) {
            $this->soap->reportGalleryPath($id, $url);
            $this->logger->notice(__METHOD__ . ' report gallery path', [
                'gallery' => $id,
                'url' => $url
            ]);
        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function consummateGallery(Contracts\GalleryImporterContract $importer, int $id)
    {
        $consummated = $importer->finish($id);

        if (!$consummated) {
            $this->logger->alert(__METHOD__, [
                'gallery' => $id
            ]);
            throw new Exceptions\SoapException("Can't consummate gallery {$id}");
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $id
        ]);
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function coverGallery(Contracts\GalleryImporterContract $importer, int $id)
    {
        $this->soap->Language('ru');

        $this->logger->debug(__METHOD__, [
            'gallery' => $id
        ]);

        $info = $this->soap->GalleryInfo($id);

        if ($info->cover) {

            try {

                $cover = $this->soap->GalleryCover($id);

                $covered = $importer->cover(
                    $id,
                    $cover
                );

                if (!$covered) {

                    $this->logger->alert(__METHOD__ . ' can\'t cover gallery', [
                        'gallery' => $id
                    ]);

                    throw new Exceptions\SoapException("Can't cover gallery {$id}");
                }

                $this->logger->notice(__METHOD__, [
                    'gallery' => $id
                ]);

            } catch (SoapFault $fault) {

                // Has no cover

                $this->logger->warning(__METHOD__ . ' has no cover', [
                    'gallery' => $id
                ]);

            }

        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    public function sourceMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        $media = $this->soap->ItemInfo($id, $gallery_id);

        if ($media->type == 'video') {

            try {

                // Download as zip

                $covered = $importer
                    ->with($gallery_id)
                    ->zip($id, $this->soap->File($id, 'mp4'));

                if (!$covered) {

                    $this->logger->alert(__METHOD__, [
                        'gallery' => $gallery_id,
                        'media' => $id
                    ]);

                    throw new Exceptions\SoapException("Can't save source for media {$id}");
                }

                $this->logger->notice(__METHOD__, [
                    'gallery' => $gallery_id,
                    'media' => $id
                ]);

                return;

            } catch (SoapFault $fault) {

            }
        }

        try {

            // Download as single file

            $covered = $importer
                ->with($gallery_id)
                ->source($id, $this->soap->File($id));

            if (!$covered) {

                $this->logger->alert(__METHOD__, [
                    'gallery' => $gallery_id,
                    'media' => $id
                ]);

                throw new Exceptions\SoapException("Can't save source for media {$id}");
            }

            $this->logger->notice(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);

            return;

        } catch (SoapFault $fault) {

        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function coverMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        try {
            $cover = $this->soap->Thumbnail($id);

            $covered = $importer
                ->with($gallery_id)
                ->cover($id, $cover);

            if (!$covered) {

                $this->logger->alert(__METHOD__, [
                    'gallery' => $gallery_id,
                    'media' => $id
                ]);

                throw new Exceptions\SoapException("Can't cover media {$id}");
            }

            $this->logger->notice(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);

        } catch (SoapFault $fault) {

        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function updateGallery(Contracts\GalleryImporterContract $importer, int $id)
    {
        $info = [];

        foreach ($this->locales as $locale) {
            try {
                $this->soap->Language($locale);
                $info[$locale] = $this->soap->GalleryInfo($id);
            } catch (SoapFault $e) {
                // Gallery not translated. Just ignore
            }
        }

        foreach ($info as $locale => $data) {

            $updated = $importer->update($id, $data, $locale);

            if (!$updated) {

                $this->logger->alert(__METHOD__, [
                    'gallery' => $id
                ]);

                throw new Exceptions\SoapException("Can't create/update gallery {$id}");
            }
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $id
        ]);

    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function revokeGallery(Contracts\GalleryImporterContract $importer, int $id)
    {
        $revoked = $importer->revoke($id);

        if (!$revoked) {

            $this->logger->alert(__METHOD__, [
                'gallery' => $id
            ]);

            throw new Exceptions\SoapException("Can't revoke gallery {$id}");
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $id
        ]);
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function revokeObsolete(Contracts\GalleryImporterContract $importer, int $gallery_id)
    {
        $external = [];

        foreach ($this->soap->ItemList($gallery_id, true) as $media) {
            $external[] = $media->id;
        }

        $local = $importer->files($gallery_id);

        $obsolete = array_diff($local, $external);

        foreach ($obsolete as $item) {
            $revoked = $importer
                ->with($gallery_id)
                ->revoke($item);

            if (!$revoked) {

                $this->logger->alert(__METHOD__, [
                    'gallery' => $gallery_id,
                    'media' => $item
                ]);

                throw new Exceptions\SoapException("Can't revoke media {$item}");
            }
        }

        if ($obsolete) {
            $this->logger->notice(__METHOD__, [
                'gallery' => $gallery_id,
                'obsolete' => $obsolete
            ]);
        }
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function revokeMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        $revoked = $importer
            ->with($gallery_id)
            ->revoke($id);

        if (!$revoked) {

            $this->logger->notice(__METHOD__, [
                'gallery' => $gallery_id,
                'media' => $id
            ]);

            throw new Exceptions\SoapException("Can't revoke media {$id}");
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $gallery_id,
            'media' => $id
        ]);
    }

    /**
     * @throws Exceptions\SoapException
     */
    protected function updateMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        $info = [];

        foreach ($this->locales as $locale) {
            try {
                $this->soap->Language($locale);
                $info[$locale] = $this->soap->ItemInfo($id, $gallery_id);
            } catch (SoapFault $e) {
                // Media not translated. Just ignore
            }
        }

        foreach ($info as $locale => $data) {
            $updated = $importer
                ->with($gallery_id)
                ->update($id, $data, $locale);

            if (!$updated) {

                $this->logger->alert(__METHOD__, [
                    'gallery' => $gallery_id,
                    'media' => $id
                ]);

                throw new Exceptions\SoapException("Can't create/update media {$id}");
            }
        }

        $this->logger->notice(__METHOD__, [
            'gallery' => $gallery_id,
            'media' => $id
        ]);
    }

    public function pushCategories(array $categories): void
    {
        $payload = [];

        foreach ($categories as $category) {
            $i = [];

            $i['id'] = $category->getId();
            $i['name'] = $category->getName();
            $i['id_parent'] = $category->getParent();
            $i['default'] = $category->getContentType();

            $payload[] = $i;
        }

        $this->soap->CategoryList($payload);

        $this->logger->notice(__METHOD__, $payload);
    }

    public function fetchGallery(Contracts\GalleryImporterContract $importer, int $gallery_id)
    {
        $this->logger->debug(__METHOD__, [
            'gallery' => $gallery_id
        ]);

        $gallery = $this->convertToUpdateStat(
            $this->soap->GalleryInfo($gallery_id)
        );

        $this->logger->debug(__METHOD__, (array)$gallery);

        $this->importGallery(
            $importer,
            $gallery
        );
    }

    public function fetchMedia(Contracts\GalleryImporterContract $importer, int $gallery_id, int $id)
    {
        $this->logger->debug(__METHOD__, [
            'gallery' => $gallery_id,
            'media' => $id
        ]);

        $media = $this->convertToUpdateStat(
            $this->soap->ItemInfo($id, $gallery_id)
        );

        $this->logger->debug(__METHOD__, (array)$media);

        $this->importMedia(
            $importer,
            $gallery_id,
            $media
        );
    }

    /**
     * @param Models\Media|Models\Gallery $object
     * @return Models\Update
     */
    protected function convertToUpdateStat($object)
    {
        return json_decode(json_encode([
            'id' => $object->id,
            'status' => $object->soap_status
        ]));
    }

    public function logger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
