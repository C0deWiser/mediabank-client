<?php


namespace Codewiser\Mediabank\Client\Models;

/**
 * @property-read integer $id
 * @property-read integer $soap_status
 * @property-read string $type e.g. video
 * @property-read integer $duration
 * @property-read string|null $subject
 * @property-read string|null $caption
 * @property-read Location $location
 * @property-read string $datetime
 * @property-read string $datetimeload
 * @property-read Author $author
 * @property-read Good $shop
 * @property-read array<Tag> $tags
 * @property-read string $editor
 * @property-read string $conditions
 * @property-read string $instruction
 * @property-read string|null $mime
 * @property-read integer $width
 * @property-read integer $height
 * @property-read integer $weight
 * @property-read integer $sort
 * @property-read boolean|null $iflag
 * @property-read string $md5
 * @property-read string|null $orig_name
 */
interface Media
{

}
