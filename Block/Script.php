<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Block;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\ViewModel\CspMarker;

class Script extends Template
{
    public function getCspMarkerAttribute(): string
    {
        $cspMarker = $this->getData('csp_marker');
        if (false === $cspMarker instanceof CspMarker) {
            return '';
        }

        return ' ' . $cspMarker->getAttribute();
    }
}
