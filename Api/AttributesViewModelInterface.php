<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api;

interface AttributesViewModelInterface
{
    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @return string
     */
    public function getAttributesAsJson(): string;

    /**
     * @param string $name
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getAttribute(string $name, $defaultValue = null);

    /**
     * @param string $name
     * @param $value
     */
    public function addAttribute(string $name, $value);

    /**
     * @return void
     */
    public function resetAttributes();

    /**
     * @param string $name
     * @return bool
     */
    public function removeAttribute(string $name): bool;
}
