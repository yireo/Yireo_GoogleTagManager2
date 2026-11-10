<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Payload;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Mapper\OrderItemDataMapper;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class CreditmemoRefund
{
    private OrderItemDataMapper $orderItemDataMapper;
    private PriceFormatter $priceFormatter;
    private Config $config;

    public function __construct(
        OrderItemDataMapper $orderItemDataMapper,
        PriceFormatter $priceFormatter,
        Config $config
    ) {
        $this->orderItemDataMapper = $orderItemDataMapper;
        $this->priceFormatter = $priceFormatter;
        $this->config = $config;
    }

    public function build(CreditmemoInterface $creditmemo, OrderInterface $order): array
    {
        $useBaseCurrency = $this->config->useBaseCurrency();

        $value = $useBaseCurrency ? (float)$creditmemo->getBaseGrandTotal() : (float)$creditmemo->getGrandTotal();
        $tax = $useBaseCurrency ? (float)$creditmemo->getBaseTaxAmount() : (float)$creditmemo->getTaxAmount();
        $shipping = $useBaseCurrency
            ? (float)$creditmemo->getBaseShippingAmount()
            : (float)$creditmemo->getShippingAmount();

        return [
            'event' => 'refund',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $useBaseCurrency ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode(),
                'value' => $this->priceFormatter->format($value),
                'tax' => $this->priceFormatter->format($tax),
                'shipping' => $this->priceFormatter->format($shipping),
                'items' => $this->getItems($creditmemo, $order),
            ],
        ];
    }

    private function getItems(CreditmemoInterface $creditmemo, OrderInterface $order): array
    {
        $items = [];
        foreach ($creditmemo->getItems() as $creditmemoItem) {
            if ((float)$creditmemoItem->getQty() <= 0) {
                continue;
            }

            $orderItem = $order->getItemById($creditmemoItem->getOrderItemId());
            if (!$orderItem || $orderItem->getParentItemId()) {
                continue;
            }

            $itemData = $this->orderItemDataMapper->mapByOrderItem($orderItem);
            $itemData['quantity'] = (float)$creditmemoItem->getQty();
            $items[] = $itemData;
        }

        return $items;
    }
}
