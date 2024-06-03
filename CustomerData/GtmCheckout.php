<?php declare(strict_types=1);

namespace Tagging\GTM\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Tagging\GTM\Api\CheckoutSessionDataProviderInterface;

class GtmCheckout implements SectionSourceInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;

    /**
     * @param CheckoutSessionDataProviderInterface $checkoutSessionDataProvider
     */
    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
    }

    /**
     * @return array
     */
    public function getSectionData(): array
    {
        $gtmEvents = $this->checkoutSessionDataProvider->get();
        $this->checkoutSessionDataProvider->clear();
        return ['gtm_events' => $gtmEvents];
    }
}
