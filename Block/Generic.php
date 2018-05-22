<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Factory\ViewModelFactory;
use Yireo\GoogleTagManager2\Helper\Data;
use Yireo\GoogleTagManager2\Model\Container;

/**
 * Class \Yireo\GoogleTagManager2\Block\Generic
 */
class Generic extends Template
{
    /**
     * @var string
     */
    protected $_template = 'generic.phtml';

    /**
     * @var ViewModelFactory
     */
    protected $viewModelFactory;

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
     * @param ViewModelFactory $viewModelFactory
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param Container $container
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        ViewModelFactory $viewModelFactory,
        Context $context,
        Session $checkoutSession,
        Data $helper,
        Container $container,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->helper = $helper;
        $this->container = $container;
        $this->checkoutSession = $checkoutSession;
        $this->order = $this->checkoutSession->getLastRealOrder();
        $this->quote = $this->checkoutSession->getQuote();
        $this->storeManager = $context->getStoreManager();
        $this->layout = $context->getLayout();
        $this->jsonEncoder = $jsonEncoder;

        parent::__construct(
            $context,
            $data
        );
    }

    public function getViewModel()
    {
        $viewModelClass = str_replace('\Block\\', '\ViewModel\\', get_class($this));
        $viewModelClass = preg_replace('/\\\Interceptor$/', '', $viewModelClass);
        return $this->viewModelFactory->create($viewModelClass);
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
        return $this->helper->isEnabled();
    }

    /**
     * Check whether this module is in debugging mode
     *
     * @return bool
     * @deprecated Use $this->getHelper()->isDebug() instead
     */
    public function isDebug()
    {
        return $this->helper->isDebug();
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
        return $this->helper->getConfigValue($key, $defaultValue);
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
        return json_encode($attributes);
    }

    /**
     * Add a new attribute to the GA container
     *
     * @param $name
     * @param $value
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
        return $this->_scopeConfig->getValue('general/store_information/name');
    }

    /**
     * @return string
     */
    public function getJsonConfiguration()
    {
        $configuration = [];

        $configuration['attributes'] = $this->getAttributes();
        $configuration['id'] = $this->getId();
        if ($this->getHelper()->isDebug()) {
            $configuration['debug'] = true;
        }

        return $this->jsonEncoder->encode($configuration);
    }
}
