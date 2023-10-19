<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\EnhancedConversions;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Config\Config;

class Sha256EmailAddress implements TagInterface
{
    private CheckoutSession $checkoutSession;
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    public function get(): string
    {
        $order = $this->getOrder();
        return hash('sha256', trim(strtolower($order->getCustomerEmail())));
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }
}
