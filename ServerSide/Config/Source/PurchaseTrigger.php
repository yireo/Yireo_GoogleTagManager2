<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PurchaseTrigger implements OptionSourceInterface
{
    public const ORDER_PLACE = 'order_place';
    public const ORDER_STATE = 'order_state';
    public const INVOICE_PAY = 'invoice_pay';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::ORDER_PLACE, 'label' => __('When the order is placed')],
            ['value' => self::ORDER_STATE, 'label' => __('When the order reaches one of the configured order states')],
            ['value' => self::INVOICE_PAY, 'label' => __('When an invoice is paid')],
        ];
    }
}
