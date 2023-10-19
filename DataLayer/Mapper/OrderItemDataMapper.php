<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Mapper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;
use AdPage\GTM\Util\PriceFormatter;

class OrderItemDataMapper
{
    private OrderRepositoryInterface $orderRepository;
    private ProductDataMapper $productDataMapper;
    private ProductRepositoryInterface $productRepository;
    private PriceFormatter $priceFormatter;
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductDataMapper $productDataMapper
     * @param ProductRepositoryInterface $productRepository
     * @param PriceFormatter $priceFormatter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductDataMapper $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter $priceFormatter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
        $this->scopeConfig = $scopeConfig;
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
            'discount' => (float) $orderItem->getDiscountAmount(),
            'quantity' => (float) $orderItem->getQtyOrdered(),
            'price' => $this->getPrice($orderItem)
        ];

        if ($orderItem->getProductType() == Configurable::TYPE_CODE) {
            $orderItemData['item_id'] = $orderItem->getProduct()->getSku(); // @phpstan-ignore-line
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

    private function getPrice(OrderItemInterface $orderItem): float
    {
        $displayType = (int)$this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            ScopeInterface::SCOPE_STORE,
            $orderItem->getStoreId()
        );

        switch ($displayType) {
            case Config::DISPLAY_TYPE_EXCLUDING_TAX:
            case Config::DISPLAY_TYPE_BOTH:
                $price = $orderItem->getPrice();
                break;
            case Config::DISPLAY_TYPE_INCLUDING_TAX:
            default:
                $price = $orderItem->getPriceInclTax();
                break;
        }

        return $this->priceFormatter->format((float)$price);
    }
}
