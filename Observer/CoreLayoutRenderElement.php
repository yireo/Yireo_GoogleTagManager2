<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Helper\Data;
use Yireo\GoogleTagManager2\ViewModel\Script;

class CoreLayoutRenderElement implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Script
     */
    private $scriptViewModel;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Data $helper
     * @param Script $scriptViewModel
     * @param Config $config
     */
    public function __construct(
        Data $helper,
        Script $scriptViewModel,
        Config $config
    ) {
        $this->helper = $helper;
        $this->scriptViewModel = $scriptViewModel;
        $this->config = $config;
    }

    /**
     * Listen to the event core_layout_render_element
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isEnabled() == false) {
            return $this;
        }

        if ($this->config->isMethodObserver() == false) {
            return $this;
        }

        $event = $observer->getEvent();
        $blockName = $event->getElementName();
        if (empty($blockName)) {
            return $this;
        }

        if ($blockName != 'root') {
            return $this;
        }

        $transport = $event->getTransport();
        $html = $transport->getHtml();

        $script = $this->scriptViewModel->getScript();

        if (empty($script)) {
            $this->helper->debug('Observer: Empty script');
            return $this;
        }

        $html = preg_replace('/\<body([^\>]+)\>/', '\0' . $script, $html);
        $this->helper->debug('Observer: Replacing header');

        $transport->setHtml($html);

        return $this;
    }
}
