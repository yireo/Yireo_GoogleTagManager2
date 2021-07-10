<?php declare(strict_types=1);
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Api\AttributesViewModelInterface;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Attributes
 */
class Attributes implements ArgumentInterface, AttributesViewModelInterface
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var Json
     */
    private $jsonEncoder;

    /**
     * Attributes constructor.
     * @param Json $jsonEncoder
     */
    public function __construct(
        Json $jsonEncoder
    ) {
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getAttributesAsJson(): string
    {
        return $this->jsonEncoder->serialize($this->getAttributes());
    }

    /**
     * @param string $name
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getAttribute(string $name, $defaultValue = null)
    {
        return $this->attributes[$name] ?? $defaultValue;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function addAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @return void
     */
    public function resetAttributes()
    {
        $this->attributes = [];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeAttribute(string $name): bool
    {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
            return true;
        }

        return false;
    }
}
