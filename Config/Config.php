<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Model\Config\Source\ViewCartOccurancesOptions;

class Config implements ArgumentInterface
{
    private ScopeConfigInterface $scopeConfig;
    private CookieHelper $cookieHelper;
    private StoreManagerInterface $storeManager;
    private AppState $appState;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CookieHelper $cookieHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CookieHelper $cookieHelper,
        AppState $appState
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cookieHelper = $cookieHelper;
        $this->appState = $appState;
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $enabled = (bool)$this->getModuleConfigValue('enabled', false);
        if (false === $enabled) {
            return false;
        }

        if (false === $this->isDeveloperMode() && false === $this->isIdValid()) {
            return false;
        }

        return true;
    }

    /**
     *
     * Get the Google tag manager url. Defaults to googletagmanager.com. when field is filled return that url.
     *
     * @return string
     */
    public function getGoogleTagmanagerUrl(): string
    {
        return $this->getModuleConfigValue(
            'serverside_gtm_url',
            'https://www.googletagmanager.com'
        );
    }

    /**
     * Check whether the module is in debugging mode
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return (bool)$this->getModuleConfigValue('debug');
    }

    /**
     * Wait for user interaction to start
     *
     * @return bool
     */
    public function waitForUserInteraction(): bool
    {
        return (bool)$this->getModuleConfigValue('wait_for_ui');
    }

    /**
     * Check whether mouse clicks are debugged as well
     *
     * @return bool
     */
    public function isDebugClicks(): bool
    {
        return $this->isDeveloperMode() && $this->isDebug() && $this->getModuleConfigValue('debug_clicks');
    }

    /**
     * Return the GA ID
     *
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->getModuleConfigValue('id');
    }

    /**
     * @return int
     */
    public function getMaximumCategoryProducts(): int
    {
        return (int)$this->getModuleConfigValue('category_products');
    }

    /**
     * @return string[]
     */
    public function getProductEavAttributeCodes(): array
    {
        return explode(',', (string)$this->getModuleConfigValue('product_eav_attributes'));
    }

    /**
     * @return string[]
     */
    public function getCategoryEavAttributeCodes(): array
    {
        return explode(',', (string)$this->getModuleConfigValue('category_eav_attributes'));
    }

    /**
     * @return string[]
     */
    public function getCustomerEavAttributeCodes(): array
    {
        return explode(',', (string)$this->getModuleConfigValue('customer_eav_attributes'));
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        $storeName = (string)$this->getConfigValue('general/store_information/name');
        if (!empty($storeName)) {
            return $storeName;
        }

        return (string)$this->storeManager->getDefaultStoreView()->getName();
    }

    /**
     * @return string
     */
    public function getCookieRestrictionModeName(): string
    {
        if ($this->cookieHelper->isCookieRestrictionModeEnabled()) {
            return CookieHelper::IS_USER_ALLOWED_SAVE_COOKIE;
        }

        return '';
    }

    /**
     * @return int
     */
    public function getCurrentWebsiteId(): int
    {
        return (int)$this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return string
     */
    public function getViewCartOccurances(): string
    {
        return $this->getModuleConfigValue('view_cart_occurances');
    }

    /**
     * @return bool
     */
    public function showViewCartEventEverywhere(): bool
    {
        return $this->getViewCartOccurances() === ViewCartOccurancesOptions::EVERYWHERE;
    }

    /**
     * @return bool
     */
    public function showViewMiniCartOnExpandOnly(): bool
    {
        return (bool)$this->getModuleConfigValue('view_cart_on_mini_cart_expand_only');
    }

    /**
     * @return string
     */
    public function getProductListValueOnCategory(): string
    {
        return (string)$this->getModuleConfigValue('product_list_value_on_category');
    }

    public function getOrderStatesForPurchaseEvent(): array
    {
        return explode(',', (string)$this->getModuleConfigValue('order_states_for_purchase_event'));
    }

    /**
     * Return a configuration value
     *
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getModuleConfigValue(string $key, $defaultValue = null, $group = 'settings')
    {
        return $this->getConfigValue('googletagmanager2/' . $group . '/' . $key, $defaultValue);
    }

    /**
     * Return a configuration value
     *
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getConfigValue(string $key, $defaultValue = null)
    {
        try {
            $value = $this->scopeConfig->getValue(
                $key,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
        } catch (NoSuchEntityException $e) {
            return $defaultValue;
        }

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * @return bool
     */
    private function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === AppState::MODE_DEVELOPER;
    }

    /**
     * @return bool
     */
    private function isIdValid(): bool
    {
        return 0 === strpos($this->getId(), 'GTM-');
    }

    /**
     * Check whether the consent component is enabled
     *
     * @return bool
     */
    public function isConsent(): bool
    {
        return (bool)$this->getModuleConfigValue(
            'enabled',
            false,
            'consent'
        );
    }

    /**
     *
     * Get the privacy statement url.
     *
     * @return string | null
     */
    public function getPrivacyStatementUrl(): string | null
    {
        return $this->getModuleConfigValue(
            'privacy_statement_url',
            null,
            'consent'
        );
    }
}
