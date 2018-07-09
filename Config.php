<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cookie\Helper\Cookie as CookieHelper;

/**
 * Class Config
 *
 * @package Yireo\GoogleTagManager2
 */
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

        if ($this->cookieHelper->isCookieRestrictionModeEnabled()) {
            return !$this->cookieHelper->isUserNotAllowSaveCookie();
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
        return (bool)$this->getConfigValue('debug');
    }

    /**
     * Return the GA ID
     *
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->getConfigValue('id');
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
     * @param null $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getConfigValue($key = null, $defaultValue = null)
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