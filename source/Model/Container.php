<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Model;

/**
 * Class \Yireo\GoogleTagManager2\Model\Container
 */
class Container extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Yireo\GoogleTagManager2\Helper\Data $helper
    )
    {
        $this->helper = $helper;

        parent::__construct($context, $coreRegistry);
    }
}
