<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Consent;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Yireo\GoogleTagManager2\ServerSide\Context\CookieReader;

class CookieConsentResolver implements ConsentResolverInterface
{
    private ScopeConfigInterface $scopeConfig;
    private CookieReader $cookieReader;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CookieReader $cookieReader
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookieReader = $cookieReader;
    }

    public function isActive(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'googletagmanager2/consent/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConsent(?int $storeId = null): ?array
    {
        return $this->cookieReader->getConsent();
    }
}
