<?php


namespace Codewiser\Mediabank\Client\Models;

/**
 * @property-read integer $id Идентификатор галереи.
 * @property-read integer $soap_status Битовая маска статуса изменения галереи.
 * @property-read string $datetime Дата публикации.
 * @property-read string|null $author Имя автора.
 * @property-read boolean $status Статус галереи (вкл/выкл)
 * @property-read integer $category_id Идентификатор целевой галереи.
 * @property-read boolean $strict_cat На категорию действуют ограничения.
 * @property-read string|null $caption Название галереи.
 * @property-read integer $cover Идентификатор медиа, используемого в качестве обложки.
 */
interface Gallery
{

}
