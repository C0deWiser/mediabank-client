<?php


namespace Codewiser\Mediabank\Client\Models;

/**
 * @property integer $id
 * @property integer $soap_status
 * @property string $type e.g. video
 * @property integer $duration
 * @property string|null $subject
 * @property string|null $caption
 * @property Location $location
 * @property string $datetime
 * @property string $datetimeload
 * @property Author $author
 * @property Good $shop
 * @property array<Tag> $tags
 * @property string $editor
 * @property string $conditions
 * @property string $instruction
 * @property string|null $mime
 * @property integer $width
 * @property integer $height
 * @property integer $weight
 * @property integer $sort
 * @property boolean|null $iflag
 * @property string $md5
 * @property string|null $orig_name
 */
interface Media
{

}
