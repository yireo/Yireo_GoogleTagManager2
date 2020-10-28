<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Helper\Data;
use Yireo\GoogleTagManager2\Model\Container;
use Yireo\GoogleTagManager2\ViewModel\Generic as GenericViewModel;
use Yireo\GoogleTagManager2\Config\Config;

class Generic extends Template
{
    /**
     * @var string
     */
    protected $_template = 'generic.phtml';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param Container $container
     * @param EncoderInterface $jsonEncoder
     * @param Config $config
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Data $helper,
        Container $container,
        EncoderInterface $jsonEncoder,
        Config $config,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->container = $container;
        $this->checkoutSession = $checkoutSession;
        $this->order = $this->checkoutSession->getLastRealOrder();
        $this->quote = $this->checkoutSession->getQuote();
        $this->storeManager = $context->getStoreManager();
        $this->layout = $context->getLayout();
        $this->jsonEncoder = $jsonEncoder;
        $this->config = $config;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return GenericViewModel
     */
    public function getViewModel()
    {
        return $this->getData('view_model');
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Return whether this module is enabled or not
     *
     * @return bool
     * @deprecated Use $this->getHelper()->isEnabled() instead
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * Check whether this module is in debugging mode
     *
     * @return bool
     * @deprecated Use $this->getHelper()->isDebug() instead
     */
    public function isDebug()
    {
        return $this->config->isDebug();
    }

    /**
     * Get the GA ID
     *
     * @return mixed
     * @deprecated Use $this->getHelper()->getId() instead
     */
    public function getId()
    {
        return $this->helper->getId();
    }

    /**
     * Return a configuration value
     *
     * @param null $key
     * @param null $defaultValue
     *
     * @return mixed
     * @deprecated Use $this->getHelper()->getConfig($key, $defaultValue) instead
     */
    public function getConfig($key = null, $defaultValue = null)
    {
        return $this->config->getConfigValue($key, $defaultValue);
    }

    /**
     * Determine whether this GA configuration has any attributes
     *
     * @return bool
     */
    public function hasAttributes()
    {
        $attributes = $this->getAttributes();
        if (!empty($attributes)) {
            return true;
        }

        return false;
    }

    /**
     * Return all attributes as JSON
     *
     * @return string
     */
    public function getAttributesAsJson()
    {
        $attributes = $this->getAttributes();
        return $this->jsonEncoder->encode($attributes);
    }

    /**
     * Add a new attribute to the GA container
     *
     * @param string $name
     * @param mixed $value
     *
     * @return object
     */
    public function addAttribute($name, $value)
    {
        return $this->container->setData($name, $value);
    }

    /**
     * Get the configured attributes for a GA container
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->container->getData();
    }

    /**
     * Return the current store information
     *
     * @return mixed
     */
    public function getWebsiteName()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/name');
    }

    /**
     * @return string
     */
    public function getJsonConfiguration()
    {
        $configuration = [];

        $configuration['cookie_restriction_mode'] = $this->config->getCookieRestrictionModeName();
        $configuration['attributes'] = $this->getAttributes();
        $configuration['id'] = $this->getId();
        if ($this->getHelper()->isDebug()) {
            $configuration['debug'] = true;
        }

        return $this->jsonEncoder->encode($configuration);
    }
}
