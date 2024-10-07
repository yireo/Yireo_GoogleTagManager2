<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;

class TriggerAddPaymentInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddPaymentInfo $addPaymentInfo;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddPaymentInfo $addPaymentInfo
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addPaymentInfo = $addPaymentInfo;
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
        AddressInterface $billingAddress = null
    ) {
        $addPaymentInfoEventData = $this->addPaymentInfo
            ->setPaymentMethod($paymentMethod->getMethod())
            ->setCartId((int)$cartId)
            ->get();
        $this->checkoutSessionDataProvider->add('add_payment_info_event', $addPaymentInfoEventData);

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
        AddressInterface $billingAddress = null
    ) {
        $addPaymentInfoEventData = $this->addPaymentInfo
            ->setPaymentMethod($paymentMethod->getMethod())
            ->setCartId((int)$cartId)
            ->get();
        $this->checkoutSessionDataProvider->add('add_payment_info_event', $addPaymentInfoEventData);
        return $orderId;
    }
}
