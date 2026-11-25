<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerAddPaymentInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddPaymentInfo $addPaymentInfo;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddPaymentInfo $addPaymentInfo,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addPaymentInfo = $addPaymentInfo;
        $this->logger = $logger;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param mixed $orderId
     * @param mixed $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->addPaymentInfoEvent($cartId, $paymentMethod);

        return $orderId;
    }

    /**
     * @param int $orderId
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return int
     */
    public function afterSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->addPaymentInfoEvent($cartId, $paymentMethod);

        return $orderId;
    }

    private function addPaymentInfoEvent($cartId, PaymentInterface $paymentMethod): void
    {
        try {
            $addPaymentInfoEventData = $this->addPaymentInfo
                ->setPaymentMethod($paymentMethod->getMethod())
                ->setCartId((int)$cartId)
                ->get();
            $this->checkoutSessionDataProvider->add('add_payment_info_event', $addPaymentInfoEventData);
        } catch (Throwable $exception) {
            $this->logger->error('GTM data layer plugin ' . self::class . ' failed: ' . $exception->getMessage());
        }
    }
}
