<?php declare(strict_types=1);

namespace AdPage\GTM\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use AdPage\GTM\Api\CheckoutSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\AddPaymentInfo;

class TriggerAddGuestPaymentInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddPaymentInfo $addPaymentInfo;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddPaymentInfo $addPaymentInfo,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addPaymentInfo = $addPaymentInfo;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    public function afterSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        $addPaymentInfoEventData = $this->addPaymentInfo
            ->setPaymentMethod($paymentMethod->getMethod())
            ->setCartId((int)$cartId)
            ->get();
        $this->checkoutSessionDataProvider->add('add_payment_info_event', $addPaymentInfoEventData);

        return $orderId;
    }

    public function afterSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        $addPaymentInfoEventData = $this->addPaymentInfo
            ->setPaymentMethod($paymentMethod->getMethod())
            ->setCartId((int)$cartId)
            ->get();
        $this->checkoutSessionDataProvider->add('add_payment_info_event', $addPaymentInfoEventData);

        return $orderId;
    }
}
