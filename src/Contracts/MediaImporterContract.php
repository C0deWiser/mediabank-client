<?php


namespace Codewiser\Mediabank\Client\Contracts;

use Codewiser\Mediabank\Client\Models\Media as MediaInfo;

/**
 * Импортирует медиа.
 */
interface MediaImporterContract extends MediabankImporter
{
    /**
     * Приложение должно скачать и сохранить файл по ссылке, и использовать его в качестве исходника фото.
     */
    public function source(int $id, string $url): bool;

    /**
     * Приложение должно скачать и разархивировать файл по ссылке, и использовать файлы в качестве исходников видео.
     */
    public function zip(int $id, string $url): bool;

    /**
     * Приложение должно создать или обновить медиа.
     *
     * @param MediaInfo $info
     */
    public function update(int $id, $info, string $locale): bool;
}
