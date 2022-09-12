<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
        CookieHelper $cookieHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $enabled = (bool)$this->getModuleConfigValue('enabled', false);
        if (!$enabled) {
            return false;
        }

        return true;
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
     * Check whether mouse clicks are debugged as well
     *
     * @return bool
     */
    public function isDebugClicks(): bool
    {
        return $this->isDebug() && $this->getModuleConfigValue('debug_clicks');
    }

    /**
     * @return bool
     */
    public function isEnhancedEcommerce(): bool
    {
        return (bool)$this->getModuleConfigValue('enhanced_ecommerce');
    }

    /**
     * Return the GA ID
     *
     * @return string
     */
    public function getId(): string
    {
        // @todo: Validate that this starts with GTM-
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
     * @return string
     */
    public function getStoreName(): string
    {
        return (string)$this->getConfigValue('general/store_information/name');
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
     * Return a configuration value
     *
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getModuleConfigValue(string $key, $defaultValue = null)
    {
        return $this->getConfigValue('googletagmanager2/settings/' . $key, $defaultValue);
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
}
