<?php


namespace Codewiser\Mediabank\Client\Models;


/**
 * @property integer $id
 * @property integer $status
 */
interface Update
{
    /**
     * Nothing new
     */
    const OK = 0;

    /**
     * New publication
     */
    const NEW = 1;

    /**
     * Changes in publication
     */
    const CAPTION = 2;

    /**
     * Changes in publication files
     */
    const UPDATE = 4;

    /**
     * Publication revoked
     */
    const DELETE = 8;

    /**
     * Publication translated
     */
    const TRANSLATED = 16;
}
