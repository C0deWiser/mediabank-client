<?php


namespace Codewiser\Mediabank\Client\Contracts;

use Codewiser\Mediabank\Client\Models\Gallery as GalleryInfo;

/**
 * Импортирующий класс.
 */
interface MediabankImporter
{
    /**
     * Приложение должно сообщить, следует ли импортировать указанный объект.
     */
    public function shouldImport(int $id): bool;

    /**
     * Приложение должно убрать указанный объект из публичного доступа.
     */
    public function revoke(int $id): bool;

    /**
     * Приложение должно создать или обновить указанный объект.
     *
     * @param GalleryInfo $info
     */
    public function update(int $id, $info, string $locale): bool;

    /**
     * Приложение должно скачать изображение и установить его в качестве обложки для указанного объекта.
     */
    public function cover(int $id, string $url): bool;

    /**
     * Приложение, если это возможно, должно вернуть публичный url указанного объекта.
     */
    public function route(int $id): ?string;

    /**
     * Приложение может сделать что-то после завершения импорта.
     */
    public function finish(int $id): bool;
}
