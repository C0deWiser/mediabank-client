<?php


namespace Codewiser\Mediabank\Client\Models;

/**
 * @property integer $id Идентификатор галереи.
 * @property integer $soap_status Битовая маска статуса изменения галереи.
 * @property string $datetime Дата публикации.
 * @property string|null $author Имя автора.
 * @property boolean $status Статус галереи (вкл/выкл)
 * @property integer $category_id Идентификатор целевой галереи.
 * @property boolean $strict_cat На категорию действуют ограничения.
 * @property string|null $caption Название галереи.
 * @property integer $cover Идентификатор медиа, используемого в качестве обложки.
 */
interface Gallery
{

}
