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

use Magento\Framework\View\Element\Template;

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
     * @var \Yireo\GoogleTagManager2\Factory\ViewModelFactory
     */
    protected $viewModelFactory;

    /**
     * @var \Yireo\GoogleTagManager2\Helper\Data
     */
    protected $helper;

    /**
     * @var \Yireo\GoogleTagManager2\Model\Container
     */
    protected $container;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper
     * @param \Yireo\GoogleTagManager2\Model\Container $container
     * @param array $data
     */
    public function __construct(
        \Yireo\GoogleTagManager2\Factory\ViewModelFactory $viewModelFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Yireo\GoogleTagManager2\Helper\Data $helper,
        \Yireo\GoogleTagManager2\Model\Container $container,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
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
     * @return \Yireo\GoogleTagManager2\Helper\Data
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

        return $this->jsonEncoder->encode($configuration);
    }
}
