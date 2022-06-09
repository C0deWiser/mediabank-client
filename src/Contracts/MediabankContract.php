<?php


namespace Codewiser\Mediabank\Client\Contracts;

use Closure;
use Carbon\Carbon;
use Codewiser\Mediabank\Client\Exceptions\SoapException;
use Psr\Log\LoggerInterface;

interface MediabankContract
{
    /**
     * Pull updates from Media Bank and proceed it with given Importer.
     *
     * @throws SoapException
     *
     * @param GalleryImporterContract|Closure $importer
     * @return int Imported galleries count.
     */
    public function fetchUpdates($importer): int;

    /**
     * Update single gallery with all its media.
     *
     * @throws SoapException
     */
    public function fetchGallery(GalleryImporterContract $importer, int $gallery_id);

    /**
     * Update single media.
     *
     * @throws SoapException
     */
    public function fetchMedia(GalleryImporterContract $importer, int $gallery_id, int $id);

    /**
     * Pull galleries, published in the given period.
     *
     * @throws SoapException
     */
    public function fetchGalleries(GalleryImporterContract $importer, Carbon $after, Carbon $before = null): int;

    /**
     * Send Categories to the Media Bank.
     *
     * @throws SoapException
     *
     * @param array<CategoryContract> $categories
     * @return void
     */
    public function pushCategories(array $categories);

    /**
     * Get service logger.
     */
    public function logger(): ?LoggerInterface;
}
