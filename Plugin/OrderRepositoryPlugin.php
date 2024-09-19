<?php declare(strict_types=1);

namespace Tagging\GTM\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    private $extensionFactory;

    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        
        if ($extensionAttributes->getTrytaggingMarketing() !== null) {
            $order->setData('trytagging_marketing', $extensionAttributes->getTrytaggingMarketing());
        }
        
        return [$order];
    }

    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        
        $trytaggingMarketing = $order->getData('trytagging_marketing');
        $extensionAttributes->setTrytaggingMarketing($trytaggingMarketing);
        $order->setExtensionAttributes($extensionAttributes);
        
        return $order;
    }
}