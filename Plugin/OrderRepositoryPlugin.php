<?php declare(strict_types=1);

namespace Tagging\GTM\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderRepositoryPlugin
{
    private $extensionFactory;

    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        return $this->addTrytaggingMarketingExtensionAttribute($order);
    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        $orders = $searchResult->getItems();
        
        foreach ($orders as &$order) {
            $this->addTrytaggingMarketingExtensionAttribute($order);
        }
        
        return $searchResult;
    }

    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getTrytaggingMarketing() !== null) {
            $order->setData('trytagging_marketing', $extensionAttributes->getTrytaggingMarketing());
        }
        
        return [$order];
    }

    private function addTrytaggingMarketingExtensionAttribute(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        
        if ($extensionAttributes->getTrytaggingMarketing() === null) {
            $trytaggingMarketing = $order->getData('trytagging_marketing');
            $extensionAttributes->setTrytaggingMarketing($trytaggingMarketing);
        }
        
        $order->setExtensionAttributes($extensionAttributes);
        
        return $order;
    }
}