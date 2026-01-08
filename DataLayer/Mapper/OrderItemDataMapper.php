<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;
use Yireo\GoogleTagManager2\Api\Data\OrderItemTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class OrderItemDataMapper
{
    private ProductDataMapper $productDataMapper;
    private ProductRepositoryInterface $productRepository;
    private PriceFormatter $priceFormatter;
    private ScopeConfigInterface $scopeConfig;
    private array $dataLayerMapping;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param ProductRepositoryInterface $productRepository
     * @param PriceFormatter $priceFormatter
     * @param ScopeConfigInterface $scopeConfig
     * @param array $dataLayerMapping
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter $priceFormatter,
        ScopeConfigInterface $scopeConfig,
        array $dataLayerMapping = []
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
        $this->scopeConfig = $scopeConfig;
        $this->dataLayerMapping = $dataLayerMapping;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return array
     */
    public function mapByOrderItem(OrderItemInterface $orderItem): array
    {
        /** @var OrderItem $orderItem */
        $orderItemData = [
            'item_id' => $orderItem->getSku(),
            'item_name' => $orderItem->getName(),
            'discount' => (float) $orderItem->getDiscountAmount(),
            'quantity' => (float) $orderItem->getQtyOrdered(),
            'price' => $this->getPrice($orderItem)
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

        return $this->parseDataLayerMapping($orderItem, $orderItemData);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return float
     */
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

    /**
     * @param OrderItemInterface $orderItem
     * @param array $data
     *
     * @return array
     */
    private function parseDataLayerMapping(OrderItemInterface $orderItem, array $data): array
    {
        if (empty($this->dataLayerMapping)) {
            return $data;
        }

        foreach ($this->dataLayerMapping as $tagName => $tagValue) {
            if (is_string($tagValue) && array_key_exists($tagValue, $data)) {
                $data[$tagName] = $data[$tagValue];
                continue;
            }

            if ($tagValue instanceof OrderItemTagInterface) {
                $tagValue->setOrderItem($orderItem);
                $data[$tagName] = $tagValue->get();
                continue;
            }

            if ($tagValue instanceof TagInterface) {
                $data[$tagName] = $tagValue->get();
            }
        }

        return $data;
    }
}
