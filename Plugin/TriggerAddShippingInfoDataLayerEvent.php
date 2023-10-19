<?php declare(strict_types=1);

namespace AdPage\GTM\Plugin;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use AdPage\GTM\Api\CheckoutSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\AddShippingInfo;

class TriggerAddShippingInfoDataLayerEvent
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddShippingInfo $addShippingInfo;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddShippingInfo $addShippingInfo
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addShippingInfo = $addShippingInfo;
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

        $event = $this->addShippingInfo->get();
        if (array_key_exists('event', $event)) {
            $this->checkoutSessionDataProvider->add('add_shipping_info_event', $event);
        }

        return $paymentDetails;
    }
}
