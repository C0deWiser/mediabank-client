<?php


namespace Codewiser\Mediabank\Client\Contracts;

/**
 * Импортирует галерею.
 */
interface GalleryImporterContract extends MediabankImporter
{
    /**
     * Должен вернуть импортёр файлов для указанной галереи.
     */
    public function with(int $id): MediaImporterContract;

    /**
     * Должен вернуть массив известных приложению идентификаторов файлов из указанной галереи.
     */
    public function files(int $id): array;
}
