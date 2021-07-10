<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api;

interface CommonsViewModelInterface
{
    /**
     * @return string
     */
    public function getBaseCurrencyCode(): string;

    /**
     * @return string
     */
    public function getPageType(): string;

    /**
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * @return string
     */
    public function getJsonConfiguration(): string;
}