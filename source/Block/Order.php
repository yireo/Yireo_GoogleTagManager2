<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

/**
 * Class Yireo\GoogleTagManager2\Block\Order
 */
class Order extends Generic
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper,
     * @param \Yireo\GoogleTagManager2\Model\Container $container
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Yireo\GoogleTagManager2\Helper\Data $helper,
        \Yireo\GoogleTagManager2\Model\Container $container,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->order = $checkoutSession->getLastRealOrder();

        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $helper,
            $container,
            $data
        );
    }

    /**
     * Return all items as array
     *
     * @return array
     */
    public function getItems()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        if (empty($order)) {
            return array();
        }

        $data = array();

        foreach($order->getAllItems() as $item) {

            /** @var \Magento\Sales\Model\Order\Item $item */

        	// Only add composed types once
        	if( $item->getParentItemId() ) {
				continue; 
			}

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $item->getProduct();
            $data[] = array(
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'category' => implode('|', $product->getCategoryIds()),
                'quantity' => $item->getQtyOrdered(),
            );
        }

        return $data;
    }

    /**
     * Return all items as JSON
     *
     * @return string
     */
    public function getItemsAsJson()
    {
        $data = $this->getItems();

        return json_encode($data);
    }
}
