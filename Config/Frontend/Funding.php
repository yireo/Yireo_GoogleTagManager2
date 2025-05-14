<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;

class Funding extends Field
{
    protected $_template = 'Yireo_GoogleTagManager2::funding.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->toHtml();
    }
}

