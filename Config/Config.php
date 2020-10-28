<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Constant for the observer method
     */
    const METHOD_OBSERVER = 0;

    /**
     * Constant for the layout method
     */
    const METHOD_LAYOUT = 1;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CookieHelper $cookieHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CookieHelper $cookieHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $enabled = (bool)$this->getConfigValue('enabled', false);
        if (!$enabled) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCookieRestrictionModeName(): string
    {
        if ((bool)$this->cookieHelper->isCookieRestrictionModeEnabled()) {
            return CookieHelper::IS_USER_ALLOWED_SAVE_COOKIE;
        }

        return '';
    }

    /**
     * Check whether the module is in debugging mode
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return (bool)$this->getConfigValue('debug');
    }

    /**
     * Return the GA ID
     *
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->getConfigValue('id');
    }

    /**
     * Check whether the insertion method is the observer method
     *
     * @return bool
     */
    public function isMethodObserver(): bool
    {
        return ($this->getConfigValue('method') == self::METHOD_OBSERVER);
    }

    /**
     * Check whether the insertion method is the layout method
     *
     * @return bool
     */
    public function isMethodLayout(): bool
    {
        return ($this->getConfigValue('method') == self::METHOD_LAYOUT);
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
        $value = $this->scopeConfig->getValue(
            'googletagmanager2/settings/' . $key,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
}
