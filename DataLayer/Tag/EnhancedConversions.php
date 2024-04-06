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
            'sha256_customer_firstname' => $this->getSha256CustomerFirstName(),
            'sha256_customer_lastname' => $this->getSha256CustomerSurname(),
            'sha256_customer_street' => $this->getSha256CustomerStreet(),
            'sha256_customer_city' => $this->getSha256CustomerCity(),
            'sha256_customer_region' => $this->getSha256CustomerRegion(),
            'sha256_customer_country' => $this->getSha256CustomerCountry(),
            'sha256_customer_postcode' => $this->getSha256CustomerPostcode(),
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

    private function getSha256CustomerFirstName(): string
    {
        return $this->sha256((string)$this->getOrder()->getCustomerFirstname());
    }


    private function getSha256CustomerSurname(): string
    {
        return $this->sha256((string)$this->getOrder()->getCustomerLastname());
    }

    private function getSha256CustomerStreet(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)implode(" ", $billingAddress->getStreet()));
    }

    private function getSha256CustomerCity(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)$billingAddress->getCity());
    }

    private function getSha256CustomerRegion(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)$billingAddress->getRegion());
    }

    private function getSha256CustomerCountry(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)$billingAddress->getCountryId());
    }

    private function getSha256CustomerPostcode(): string
    {
        $billingAddress = $this->getOrder()->getBillingAddress();
        if (false === $billingAddress instanceof OrderAddressInterface) {
            return '';
        }
        return $this->sha256((string)$billingAddress->getPostcode());
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
