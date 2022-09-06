<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Yireo\GoogleTagManager2\Config\Config;

class CheckoutConfiguration implements TagInterface
{
    private Config $config;

    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    public function get(): array
    {
        return [
            'trackCartAsCheckoutStep' => $this->config->isDataLayerTrackCartAsCheckoutStep(),
            'steps' => [
                'cart' => $this->config->getDataLayerCartStep(),
                'shipping' => $this->config->getDataLayerCheckoutShippingStep(),
                'payment' => $this->config->getDataLayerCheckoutPaymentStep()
            ]
        ];
    }
}
