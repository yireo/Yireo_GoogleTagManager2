<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Factory;

use Magento\Framework\ObjectManagerInterface;
use Yireo\GoogleTagManager2\ViewModel\Generic;

class ViewModelFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ViewModelFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className)
    {
        if (!class_exists($className)) {
            return $this->createGeneric();
        }

        return $this->objectManager->create($className);
    }

    /**
     * @return object
     */
    private function createGeneric()
    {
        return $this->objectManager->create(Generic::class);
    }
}
