<?php


namespace Codewiser\Mediabank\Client\Contracts;

/**
 * Категория, в которые осуществляется публикация импортируемых медиа.
 */
interface CategoryContract
{
    /**
     * Первичный ключ категории.
     */
    public function getId(): int;

    /**
     * Название категории.
     */
    public function getName(): string;

    /**
     * Родительская категория (0 — если нет).
     */
    public function getParent(): int;

    /**
     * Тип категории (ожидается video или photo).
     */
    public function getContentType(): string;
}
