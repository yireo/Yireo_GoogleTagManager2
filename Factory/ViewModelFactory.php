<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Factory;

/**
 * Class \Yireo\GoogleTagManager2\Factory\ViewModelFactory
 */
class ViewModelFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ViewModelFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
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
        return $this->objectManager->create(\Yireo\GoogleTagManager2\ViewModel\Generic::class);
    }
}