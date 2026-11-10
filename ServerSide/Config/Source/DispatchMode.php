<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DispatchMode implements OptionSourceInterface
{
    public const CRON = 'cron';
    public const QUEUE = 'queue';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::CRON, 'label' => __('Cron')],
            ['value' => self::QUEUE, 'label' => __('Message queue')],
        ];
    }
}
