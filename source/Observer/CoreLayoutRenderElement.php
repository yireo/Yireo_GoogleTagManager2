<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\ObserverInterface;

class CoreLayoutRenderElement implements ObserverInterface
{
    /**
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper
     */
    public function __construct(
        \Yireo\GoogleTagManager2\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * Listen to the event core_layout_render_element
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled() == false) {
            return $this;
        }

        if ($this->helper->isMethodObserver() == false) {
            return $this;
        }

        $event = $observer->getEvent();
        $blockName = $event->getElementName();
        if(empty($blockName)) {
            return $this;
        }

        if($blockName != 'root') {
            return $this;
        }

        $transport = $event->getTransport();
        $html = $transport->getHtml();

        $script = $this->helper->getHeaderScript();

        if (empty($script)) {
            $this->helper->debug('Observer: Empty script');
            return $this;
        }

        $html = preg_replace('/\<body([^\>]+)\>/', '\0'.$script, $html);
        $this->helper->debug('Observer: Replacing header');

        $transport->setHtml($html);

        return $this;
    }
}
