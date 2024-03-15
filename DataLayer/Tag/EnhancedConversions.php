<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Api\Data\MergeTagInterface;

class EnhancedConversions implements MergeTagInterface
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

    public function merge(): array
    {
        return [
            'sha256_email_address' => $this->getSha256EmailAddress(),
            'sha256_customer_telephone' => $this->getSha256CustomerTelephone(),
        ];
    }

    private function getSha256EmailAddress(): string
    {
        return $this->sha256((string)$this->getOrder()->getCustomerEmail());
    }

    private function getSha256CustomerTelephone(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)$billingAddress->getTelephone());
    }

    private function sha256(string $value): string
    {
        return hash('sha256', trim(strtolower($value)));
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }
}
