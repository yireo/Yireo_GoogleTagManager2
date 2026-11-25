<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Consent;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\ServerSide\Context\CookieReader;

class CookieConsentResolver implements ConsentResolverInterface
{
    private ScopeConfigInterface $scopeConfig;
    private CookieReader $cookieReader;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CookieReader $cookieReader,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookieReader = $cookieReader;
        $this->storeManager = $storeManager;
    }

    public function isActive(?int $storeId = null): bool
    {
        return $this->isConsentBannerEnabled($storeId) || $this->isCookieRestrictionEnabled($storeId);
    }

    public function getConsent(?int $storeId = null): ?array
    {
        if ($this->isConsentBannerEnabled($storeId) || false === $this->isCookieRestrictionEnabled($storeId)) {
            return $this->cookieReader->getConsent();
        }

        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $allowed = $this->cookieReader->isCookieRestrictionAllowed($websiteId);
        if ($allowed === null) {
            return null;
        }

        return [
            'analytics' => $allowed,
            'marketing' => $allowed,
            'preferences' => $allowed,
        ];
    }

    private function isConsentBannerEnabled(?int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'googletagmanager2/consent/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function isCookieRestrictionEnabled(?int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'web/cookie/cookie_restriction',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
