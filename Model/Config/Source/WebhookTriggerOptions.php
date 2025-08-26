<?php declare(strict_types=1);

namespace Tagging\GTM\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WebhookTriggerOptions implements OptionSourceInterface
{
    const DEFAULT = 'default';
    const ON_ORDER_STATE = 'on_order_state';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::DEFAULT, 'label' => __('Default (total paid)')],
            ['value' => self::ON_ORDER_STATE, 'label' => __('On order state')],
        ];

        return $options;
    }
}
