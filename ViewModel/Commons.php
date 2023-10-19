<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace AdPage\GTM\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use AdPage\GTM\Config\Config;

/**
 * Class \AdPage\GTM\ViewModel\Commons
 */
class Commons implements ArgumentInterface
{
    private DataLayer $dataLayer;
    private Config $config;
    private SerializerInterface $serializer;

    /**
     * Commons constructor.
     *
     * @param DataLayer $dataLayer
     * @param Config $config
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DataLayer $dataLayer,
        Config $config,
        SerializerInterface $serializer
    ) {
        $this->dataLayer = $dataLayer;
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        $configuration = [];
        $configuration['cookie_restriction_mode'] = $this->config->getCookieRestrictionModeName();
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
        return $this->serializer->serialize($this->getConfiguration());
    }
}
