<?php

namespace Codewiser\Mediabank\Client\Models;

/**
 * @method boolean Login(string $login, string $password, string $locale) Авторизация
 * @method void Language(string $locale) Установить язык возвращаемых данных
 *
 * @method Update[] GalleryList() Получить список новых/измененный публикаций
 * @method Update[] GalleryListByDate(int $dateAfter, int $dateBefore = 0) Получить список публикаций в заданный период
 * @method Gallery GalleryInfo(int $gallery_id) Получить свойства публикации
 * @method string GalleryCover(int $gallery_id, int $width = 0, int $height = 0, string $algorithm = 'crop') Получить ссылку на скачивание обложки
 *
 * @method Update[] ItemList(int $gallery_id, bool $all = false) Получить список файлов в публикации
 * @method Media ItemInfo(int $media_id, int $gallery_id) Получить свойства файла
 *
 * @method boolean Finish(int $gallery_id, int $media_id = null) Завершить публикацию
 *
 * @method string File(int $media_id, string $ext = null) Получить ссылку на скачивание файла
 * @method string Thumbnail(int $media_id, int $width = 0, int $height = 0, string $algorithm = '') Получить ссылку на скачивание миниатюры
 *
 * @method void reportGalleryPath(int $gallery_id, string $url) Сообщить адрес принятой публикации
 *
 * @method void CategoryList(array $categories) Отправить список категорий
 *
 */
class Client extends \SoapClient
{

}
