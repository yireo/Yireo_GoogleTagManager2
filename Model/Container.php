<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Model;

/**
 * Class \Yireo\GoogleTagManager2\Model\Container
 */
class Container extends \Magento\Framework\DataObject
{
    /**
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Yireo\GoogleTagManager2\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($data);
    }
}
