<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Config\Config;

class GtmCheckout implements SectionSourceInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private Config $config;

    /**
     * @param CheckoutSessionDataProviderInterface $checkoutSessionDataProvider
     * @param Config $config
     */
    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        Config $config
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getSectionData(): array
    {
        if (false === $this->config->isEnabled()) {
            $this->checkoutSessionDataProvider->clear();
            return [];
        }

        $gtmEvents = $this->checkoutSessionDataProvider->get();
        $this->checkoutSessionDataProvider->clear();
        return ['gtm_events' => $gtmEvents];
    }
}
