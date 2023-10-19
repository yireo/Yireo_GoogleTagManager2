<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Mapper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use AdPage\GTM\Config\Config;
use AdPage\GTM\Util\PriceFormatter;

class OrderDataMapper
{
    private PriceFormatter $priceFormatter;
    private GuestDataMapper $guestDataMapper;
    private CustomerDataMapper $customerDataMapper;
    private CustomerRepositoryInterface $customerRepository;
    private Config $config;

    /**
     * @param PriceFormatter $priceFormatter
     * @param GuestDataMapper $guestDataMapper
     * @param CustomerDataMapper $customerDataMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $config
     */
    public function __construct(
        PriceFormatter $priceFormatter,
        GuestDataMapper $guestDataMapper,
        CustomerDataMapper $customerDataMapper,
        CustomerRepositoryInterface $customerRepository,
        Config $config
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->guestDataMapper = $guestDataMapper;
        $this->customerDataMapper = $customerDataMapper;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mapByOrder(OrderInterface $order): array
    {
        return [
            'currency' => $order->getOrderCurrencyCode(),
            'value' => $this->getValueFromOrder($order),
            'id' => $order->getIncrementId(),
            'affiliation' => $this->config->getStoreName(),
            'revenue' => $this->priceFormatter->format($order->getGrandTotal()),
            'discount' => $this->priceFormatter->format((float)$order->getDiscountAmount()),
            'shipping' => $this->priceFormatter->format((float)$order->getShippingAmount()),
            'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
            'coupon' => $order->getCouponCode(),
            'date' => date("Y-m-d", strtotime($order->getCreatedAt())),
            'paymentType' => $this->getPaymentType($order),
            'customer' => $this->getCustomerData($order),
        ];
    }

    /**
     * @param OrderInterface $order
     * @return float
     */
    private function getValueFromOrder(OrderInterface $order): float
    {
        return $this->priceFormatter->format((float)$order->getGrandTotal());
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCustomerData(OrderInterface $order): array
    {
        if ($order->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            return $this->customerDataMapper->mapByCustomer($customer);
        }

        return $this->guestDataMapper->mapByOrder($order);
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws LocalizedException
     */
    protected function getPaymentType(OrderInterface $order): string
    {
        // @phpstan-ignore-next-line
        if (!$order || !$order->getPayment() || !$order->getPayment()->getMethodInstance()) {
            return '';
        }

        // @phpstan-ignore-next-line
        return $order->getPayment()->getMethodInstance()->getTitle();
    }
}
