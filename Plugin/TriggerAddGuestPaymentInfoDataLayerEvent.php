<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerAddGuestPaymentInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddPaymentInfo $addPaymentInfo;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddPaymentInfo $addPaymentInfo,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addPaymentInfo = $addPaymentInfo;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->logger = $logger;
    }

    public function afterSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->addPaymentInfoEvent($cartId, $paymentMethod);

        return $orderId;
    }

    public function afterSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
        $orderId,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->addPaymentInfoEvent($cartId, $paymentMethod);

        return $orderId;
    }

    private function addPaymentInfoEvent($maskedCartId, PaymentInterface $paymentMethod): void
    {
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
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
