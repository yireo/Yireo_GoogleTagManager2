<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class OrderItemDataMapper
{
    private OrderRepositoryInterface $orderRepository;
    private ProductDataMapper $productDataMapper;
    private ProductRepositoryInterface $productRepository;
    private PriceFormatter $priceFormatter;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductDataMapper $productDataMapper
     * @param ProductRepositoryInterface $productRepository
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductDataMapper $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter $priceFormatter
    ) {
        $this->orderRepository = $orderRepository;
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return array
     */
    public function mapByOrderItem(OrderItemInterface $orderItem): array
    {
        $order = $this->orderRepository->get($orderItem->getOrderId());

        $orderItemData = [
            'item_id' => $orderItem->getId(),
            'item_name' => $orderItem->getName(),
            'currency' => $order->getOrderCurrencyCode(),
            'discount' => $orderItem->getDiscountAmount(),
            'quantity' => $orderItem->getQtyOrdered(),
            'price' => $this->priceFormatter->format((float)$orderItem->getPriceInclTax())
        ];

        try {
            $product = $this->productRepository->get($orderItem->getSku());
        } catch (NoSuchEntityException $e) {
            return $orderItemData;
        }

        $orderItemData = array_merge_recursive($orderItemData, $this->productDataMapper->mapByProduct($product));

        unset($orderItemData['details']['availability']);
        if (empty($orderItemData['details'])) {
            unset($orderItemData['details']);
        }

        return $orderItemData;
    }
}
