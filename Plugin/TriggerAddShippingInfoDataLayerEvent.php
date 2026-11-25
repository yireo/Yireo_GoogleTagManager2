<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddShippingInfo;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerAddShippingInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddShippingInfo $addShippingInfo;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddShippingInfo $addShippingInfo,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addShippingInfo = $addShippingInfo;
        $this->logger = $logger;
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param mixed $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetailsInterface
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        PaymentDetailsInterface $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        try {
            $event = $this->addShippingInfo->get();
            if (array_key_exists('event', $event)) {
                $this->checkoutSessionDataProvider->add('add_shipping_info_event', $event);
            }
        } catch (Throwable $exception) {
            $this->logger->error('GTM data layer plugin ' . self::class . ' failed: ' . $exception->getMessage());
        }

        return $paymentDetails;
    }
}
