<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;

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
        return ['gtm_events' => $gtmEvents];
    }
}
