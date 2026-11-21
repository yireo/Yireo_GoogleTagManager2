<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;

class BrowserPurchaseSuppressor
{
    private Config $config;
    private ConsentResolverInterface $consentResolver;

    public function __construct(
        Config $config,
        ConsentResolverInterface $consentResolver
    ) {
        $this->config = $config;
        $this->consentResolver = $consentResolver;
    }

    public function isSuppressed(OrderInterface $order): bool
    {
        $storeId = (int)$order->getStoreId();
        if (false === $this->config->suppressBrowserPurchase($storeId)) {
            return false;
        }

        if (false === $this->config->requireConsent($storeId)) {
            return true;
        }

        $consent = $this->consentResolver->getConsent($storeId);

        return is_array($consent) && ($consent['analytics'] ?? false) === true;
    }
}
