<?php
/**
 * GoogleTagManager plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Block;

use Yireo\GoogleTagManager2\ViewModel\Custom as CustomViewModel;

class Custom extends Generic
{
    /**
     * @var string
     */
    protected $_template = 'custom.phtml';

    /**
     * @return CustomViewModel
     */
    public function getViewModel()
    {
        return $this->getData('view_model');
    }
}
