<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Yireo\GoogleTagManager2\Api\CommonsViewModelInterface;
use Yireo\GoogleTagManager2\Config\Config;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Commons
 */
class Commons implements ArgumentInterface, CommonsViewModelInterface
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var Attributes
     */
    private $attributesViewModel;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * Commons constructor.
     *
     * @param Currency $currency
     * @param Attributes $attributesViewModel
     * @param Json $jsonSerializer
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        Currency $currency,
        Attributes $attributesViewModel,
        Json $jsonSerializer,
        RequestInterface $request,
        Config $config
    ) {
        $this->currency = $currency;
        $this->attributesViewModel = $attributesViewModel;
        $this->jsonSerializer = $jsonSerializer;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode(): string
    {
        return (string)$this->currency->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        $moduleName = $this->request->getModuleName();
        $controllerName = $this->request->getControllerName();
        $actionName = $this->request->getActionName();
        return $moduleName . '/' . $controllerName . '/' . $actionName;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        $configuration = [];
        $configuration['cookie_restriction_mode'] = $this->config->getCookieRestrictionModeName();
        $configuration['attributes'] = $this->attributesViewModel->getAttributes();
        $configuration['id'] = $this->config->getId();
        $configuration['debug'] = $this->config->isDebug();
        return $configuration;
    }

    /**
     * @return string
     */
    public function getJsonConfiguration(): string
    {
        $configuration = $this->getConfiguration();
        $this->attributesViewModel->resetAttributes();
        return $this->jsonSerializer->serialize($configuration);
    }
}
