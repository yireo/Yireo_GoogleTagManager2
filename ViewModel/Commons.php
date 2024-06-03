<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Tagging\GTM\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Tagging\GTM\Config\Config;

/**
 * Class \Tagging\GTM\ViewModel\Commons
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
        $configuration['data_layer'] = $this->dataLayer->getDataLayer();
        $configuration['config'] = $this->config->getConfig();
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
