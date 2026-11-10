<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Events implements OptionSourceInterface
{
    public const EVENTS = [
        'purchase',
        'refund',
        'add_to_cart',
        'remove_from_cart',
        'begin_checkout',
        'add_shipping_info',
        'add_payment_info',
        'login',
        'sign_up',
        'add_to_wishlist',
    ];

    public function toOptionArray(): array
    {
        $options = [];
        foreach (self::EVENTS as $event) {
            $options[] = ['value' => $event, 'label' => $event];
        }

        return $options;
    }
}
