<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

/**
 * Class \Yireo\GoogleTagManager2\Block\Script
 */
class Script extends Generic
{
    /**
     * Return the JavaScript for insertion in the HTML header
     *
     * @return string
     */
    public function getScript()
    {
        return $this->helper->getHeaderScript();
    }
}
