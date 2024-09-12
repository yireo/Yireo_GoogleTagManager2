<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order;

class OrderStateOptions implements OptionSourceInterface
{

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getStates() as $state) {
            $options[] = ['value' => $state, 'label' => $state];
        }

        return $options;
    }

    private function getStates(): array
    {
        return [
            Order::STATE_NEW,
            Order::STATE_PAYMENT_REVIEW,
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE,
            Order::STATE_CANCELED,
            Order::STATE_CLOSED,
            Order::STATE_HOLDED,
        ];
    }
}
