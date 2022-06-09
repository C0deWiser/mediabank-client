<?php


namespace Codewiser\Mediabank\Client\Contracts;

use Psr\Log\LoggerInterface;

interface VideoPlayerProvider
{
    /**
     * Get Video Player identifier from FCZ.
     */
    public function getVideoPlayerIdentifier(int $gallery_id): ?int;

    /**
     * Get service logger.
     */
    public function logger(): ?LoggerInterface;
}
