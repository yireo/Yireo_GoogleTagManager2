<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Config\Config;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Commons
 */
class Commons implements ArgumentInterface
{
    private DataLayer $dataLayer;
    private Config $config;

    /**
     * Commons constructor.
     *
     * @param DataLayer $dataLayer
     * @param Config $config
     */
    public function __construct(
        DataLayer $dataLayer,
        Config $config
    ) {
        $this->dataLayer = $dataLayer;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        $configuration = [];
        $configuration['cookie_restriction_mode'] = $this->config->getCookieRestrictionModeName();
        $configuration['website_id'] = $this->config->getCurrentWebsiteId();
        $configuration['data_layer'] = $this->dataLayer->getDataLayer();
        $configuration['id'] = $this->config->getId();
        $configuration['debug'] = $this->config->isDebug();
        $configuration['gtm_url'] = $this->config->getGoogleTagmanagerUrl();
        return $configuration;
    }

    /**
     * @return string
     */
    public function getConfigurationAsJson(): string
    {
        return $this->dataLayer->toJson($this->getConfiguration());
    }
}
