<?php declare(strict_types=1);

namespace Tagging\GTM\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepository
{
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes->setTrytaggingMarketing($order->getData('trytagging_marketing'));
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes->setTrytaggingMarketing($order->getData('trytagging_marketing'));
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}