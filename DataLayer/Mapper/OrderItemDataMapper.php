<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
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
        OrderRepositoryInterface   $orderRepository,
        ProductDataMapper          $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter             $priceFormatter
    )
    {
        $this->orderRepository = $orderRepository;
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return array
     */
    public function mapByOrderItem(OrderItemInterface $orderItem, ?OrderInterface $order = null): array
    {
        if (!$order instanceof OrderInterface) {
            $order = $this->orderRepository->get($orderItem->getOrderId());
        }

        $orderItemData = [
            'item_id' => $orderItem->getSku(),
            'item_name' => $orderItem->getName(),
            'currency' => $order->getOrderCurrencyCode(),
            'discount' => $orderItem->getDiscountAmount(),
            'quantity' => $orderItem->getQtyOrdered(),
            'price' => $this->priceFormatter->format((float)$orderItem->getPriceInclTax())
        ];

        if ($orderItem->getProductType() == Configurable::TYPE_CODE) {
            $orderItemData['item_id'] = $orderItem->getProduct()->getSku();
            $orderItemData['item_variant'] = $orderItem->getSku();
        }

        try {
            $product = $this->productRepository->get($orderItem->getSku());
        } catch (NoSuchEntityException $e) {
            return $orderItemData;
        }

        $orderItemData = array_replace_recursive($this->productDataMapper->mapByProduct($product), $orderItemData);

        unset($orderItemData['details']['availability']);
        if (empty($orderItemData['details'])) {
            unset($orderItemData['details']);
        }

        return $orderItemData;
    }
}
