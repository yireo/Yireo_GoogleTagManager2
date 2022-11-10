<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\OrderItemDataMapper;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class OrderItems implements TagInterface
{
    private CheckoutSession $checkoutSession;
    private OrderItemDataMapper $orderItemDataMapper;
    private ?OrderInterface $order;

    /**
     * @param CheckoutSession $checkoutSession
     * @param OrderItemDataMapper $orderItemDataMapper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderItemDataMapper $orderItemDataMapper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderItemDataMapper = $orderItemDataMapper;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $order = $this->order;
        if (empty($order)) {
            $order = $this->checkoutSession->getLastRealOrder();
        }

        $orderItemsData = [];
        foreach ($order->getAllItems() as $item) {
            $orderItemsData[] = $this->orderItemDataMapper->mapByOrderItem($item);
        }

        return $orderItemsData;
    }

    /**
     * @param OrderInterface $order
     * @return OrderItems
     */
    public function setOrder(OrderInterface $order): OrderItems
    {
        $this->order = $order;
        return $this;
    }
}
