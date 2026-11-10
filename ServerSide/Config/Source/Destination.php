<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Destination implements OptionSourceInterface
{
    public const GA4 = 'ga4';
    public const SGTM = 'sgtm';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::GA4, 'label' => __('Google Analytics 4 (Measurement Protocol)')],
            ['value' => self::SGTM, 'label' => __('Server-side GTM container')],
        ];
    }
}
