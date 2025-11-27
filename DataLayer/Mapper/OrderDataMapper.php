<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\MethodInterface as PaymentMethod;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Yireo\GoogleTagManager2\Api\Data\OrderTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\OrderTotals;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class OrderDataMapper
{
    private PriceFormatter $priceFormatter;
    private GuestDataMapper $guestDataMapper;
    private CustomerDataMapper $customerDataMapper;
    private CustomerRepositoryInterface $customerRepository;
    private Config $config;
    private OrderTotals $orderTotals;
    private array $dataLayerMapping;

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
        Config $config,
        OrderTotals $orderTotals,
        array $dataLayerMapping = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->guestDataMapper = $guestDataMapper;
        $this->customerDataMapper = $customerDataMapper;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->orderTotals = $orderTotals;
        $this->dataLayerMapping = $dataLayerMapping;
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mapByOrder(OrderInterface $order): array
    {
        $orderData= [
            'currency' => $order->getOrderCurrencyCode(),
            'value' => $this->priceFormatter->format($this->orderTotals->getValueTotal($order)),
            'id' => $order->getIncrementId(),
            'affiliation' => $this->config->getStoreName(),
            'revenue' => $this->priceFormatter->format($this->orderTotals->getValueTotal($order)),
            'discount' => $this->priceFormatter->format((float)$order->getDiscountAmount()),
            'shipping' => $this->priceFormatter->format($this->orderTotals->getShippingTotal($order)),
            'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
            'coupon' => $order->getCouponCode(),
            'date' => date("Y-m-d", strtotime($order->getCreatedAt())),
            'paymentType' => $this->getPaymentType($order),
            'payment_method' => $order->getPayment() ? $order->getPayment()->getMethod() : '',
            'payment_type' => $order->getPayment() ? $order->getPayment()->getMethod() : '',
            'customer' => $this->getCustomerData($order),
        ];

        return $this->parseDataLayerMapping($order, $orderData);
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

        /** @var Order $order */
        return $this->guestDataMapper->mapByOrder($order);
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws LocalizedException
     */
    protected function getPaymentType(OrderInterface $order): string
    {
        $orderPayment = $order->getPayment();
        if (!$orderPayment instanceof Payment) {
            return '';
        }

        $paymentMethod = $orderPayment->getMethodInstance();
        if (!$paymentMethod instanceof PaymentMethod) {
            return '';
        }

        return $paymentMethod->getTitle();
    }

    /**
     * @param OrderInterface $order
     * @param array          $data
     *
     * @return array
     */
    private function parseDataLayerMapping(OrderInterface $order, array $data): array
    {
        if (empty($this->dataLayerMapping)) {
            return [];
        }

        foreach ($this->dataLayerMapping as $tagName => $tagValue) {
            if (is_string($tagValue) && array_key_exists($tagValue, $data)) {
                $data[$tagName] = $data[$tagValue];
                continue;
            }

            if ($tagValue instanceof OrderTagInterface) {
                $tagValue->setOrder($order);
                $data[$tagName] = $tagValue->get();
                continue;
            }

            if ($tagValue instanceof TagInterface) {
                $data[$tagName] = $tagValue->get();
            }
        }

        return $data;
    }
}
